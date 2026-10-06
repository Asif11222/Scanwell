@extends('layouts.admin')

@section('title', 'ScanWell Admin - App Control')
@section('breadcrumb', 'App Control')
@section('page_title', 'App Control')

@section('content')
<div class="page-head">
  <div>
    <h2>App Control</h2>
    <p>Runtime remote configuration for service availability, Flutter mobile version policies, and feature flags</p>
  </div>
</div>

<div class="info-strip warning">
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
  <div class="copy">
    <strong>High-impact controls take effect instantly for all active mobile app users.</strong>
    <span>Enabling maintenance mode or changing the minimum version requires confirmation and generates an audit log entry.</span>
  </div>
</div>

<div class="grid grid-2">
  <!-- Release & Availability Policies -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <h3>Release & Availability</h3>
        <p>Mobile client version and maintenance gating</p>
      </div>
    </div>
    <div class="card-body">
      <form id="versionForm" method="POST" action="{{ route('admin.appControl.update') }}">
        @csrf
        <div class="form-grid">
          <div class="field">
            <label>Minimum Supported Version</label>
            <input name="value" class="input" value="{{ $controls['minVersion']->value ?? '1.4.0' }}" onchange="saveAppSetting('minVersion', this.value, 'string')">
            <input type="hidden" name="key" value="minVersion">
          </div>
          <div class="field">
            <label>Latest App Version in Stores</label>
            <input class="input" value="{{ $controls['latestVersion']->value ?? '1.7.2' }}" onchange="saveAppSetting('latestVersion', this.value, 'string')">
          </div>
          <div class="field full">
            <label>Maintenance Advisory Message</label>
            <textarea class="textarea" onchange="saveAppSetting('maintenanceMessage', this.value, 'string')">{{ $controls['maintenanceMessage']->value ?? 'ScanWell is temporarily unavailable while we perform updates.' }}</textarea>
          </div>
        </div>
      </form>

      <div class="control-list" style="margin-top:16px">
        <div class="control-row">
          <div class="ci">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
          </div>
          <div class="control-copy">
            <strong>Maintenance Mode</strong>
            <p>Temporarily block normal app usage and display the configured maintenance message.</p>
          </div>
          @php $maint = filter_var($controls['maintenanceMode']->value ?? 'false', FILTER_VALIDATE_BOOLEAN); @endphp
          <button type="button" class="toggle {{ $maint ? 'on' : '' }}" onclick="confirmToggle('maintenanceMode', 'Maintenance Mode')"></button>
        </div>

        <div class="control-row">
          <div class="ci">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M6 11l6-6 6 6"/></svg>
          </div>
          <div class="control-copy">
            <strong>Force Update</strong>
            <p>Require users with app versions below the minimum supported version to update from Play Store.</p>
          </div>
          @php $force = filter_var($controls['forceUpdate']->value ?? 'false', FILTER_VALIDATE_BOOLEAN); @endphp
          <button type="button" class="toggle {{ $force ? 'on' : '' }}" onclick="confirmToggle('forceUpdate', 'Force Update')"></button>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile Feature Flags -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <h3>Feature Flags</h3>
        <p>Turn major mobile capabilities on or off remotely</p>
      </div>
    </div>
    <div class="card-body">
      <div class="control-list">
        @php
          $flags = [
            ['registration', 'Registration Enabled', 'Allow new users to sign up for ScanWell accounts.'],
            ['scanning', 'Product Scanning Enabled', 'Allow barcode and camera scanning on mobile devices.'],
            ['ocr', 'OCR Processing Enabled', 'Allow label images to enter extraction processing.'],
            ['comparison', 'Product Comparison Enabled', 'Show side-by-side product nutrition comparison.'],
            ['personalizedAlerts', 'Personalized Alerts Enabled', 'Show health profile caution badges after user opt-in.'],
            ['contributorMode', 'Contributor Mode Enabled', 'Allow community users to submit packaging photos and corrections.'],
          ];
        @endphp

        @foreach($flags as $flag)
          @php
            $enabled = filter_var($controls[$flag[0]]->value ?? 'true', FILTER_VALIDATE_BOOLEAN);
          @endphp
          <div class="control-row">
            <div class="ci">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3"/><path d="M1 14h6M9 8h6M17 16h6"/></svg>
            </div>
            <div class="control-copy">
              <strong>{{ $flag[1] }}</strong>
              <p>{{ $flag[2] }}</p>
            </div>
            <button type="button" class="toggle {{ $enabled ? 'on' : '' }}" onclick="toggleAppFlag('{{ $flag[0] }}')"></button>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function toggleAppFlag(key) {
    fetch(`{{ route('admin.appControl.toggle') }}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ key })
    })
    .then(res => res.json())
    .then(data => {
      showToast(data.message, 'success', 'Feature Flag');
      setTimeout(() => location.reload(), 500);
    });
  }

  function confirmToggle(key, label) {
    openModal({
      title: `Confirm High-Impact Control: ${label}`,
      subtitle: 'This will affect all active mobile clients on next API synchronization',
      body: `
        <div class="info-strip warning">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <div class="copy">
            <strong>Are you sure you want to toggle ${label}?</strong>
            <span>This setting will be logged in the audit trail with your admin credentials.</span>
          </div>
        </div>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="closeModal();toggleAppFlag('${key}')">Confirm Change</button>
      `
    });
  }

  function saveAppSetting(key, value, type) {
    fetch(`{{ route('admin.appControl.update') }}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ key, value, type })
    })
    .then(res => res.json())
    .then(data => {
      showToast(data.message, 'success', 'Setting Saved');
    });
  }
</script>
@endpush
@endsection
