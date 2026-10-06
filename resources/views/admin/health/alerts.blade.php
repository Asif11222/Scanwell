@extends('layouts.admin')

@section('title', 'ScanWell Admin - Personalized Alerts')
@section('breadcrumb', 'Personalized Alerts')
@section('page_title', 'Personalized Alerts')

@section('content')
<div class="page-head">
  <div>
    <h2>Personalized In-App Alerts</h2>
    <p>Configure condition-based alert messages that appear when users scan products matching their selected health profile</p>
  </div>
</div>

<div class="split-panel">
  <!-- Alerts List -->
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Condition</th>
            <th>Trigger Rule</th>
            <th>Alert Copy</th>
            <th>Severity</th>
            <th>Priority</th>
            <th>Status</th>
            <th style="text-align:right">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($alerts as $a)
            <tr>
              <td><strong>{{ $a->condition }}</strong></td>
              <td><code>{{ $a->trigger }}</code></td>
              <td>
                <div class="cell-main">{{ $a->title }}</div>
                <div class="cell-sub" style="max-width:240px">{{ $a->message }}</div>
              </td>
              <td>
                @php
                  $sevClass = strtolower($a->severity) === 'red' ? 'red' : (strtolower($a->severity) === 'yellow' ? 'yellow' : 'green');
                @endphp
                <span class="severity {{ $sevClass }}">
                  <i></i>
                  {{ $a->severity }}
                </span>
              </td>
              <td>#{{ $a->priority }}</td>
              <td>
                @php $st = strtolower($a->status); @endphp
                <span class="badge {{ $st }}">{{ $a->status }}</span>
              </td>
              <td>
                <div class="actions">
                  <button type="button" class="mini-btn" title="Simulate in Mobile App" onclick="previewMobileAlert('{{ $a->condition }}', '{{ $a->severity }}', '{{ addslashes($a->title) }}', '{{ addslashes($a->message) }}', '{{ addslashes($a->recommendation) }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M11 18h2"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" style="padding:32px;text-align:center;color:var(--ink-400)">No alerts configured.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Mobile Live Phone Simulator -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <h3>Live Mobile App Emulator</h3>
        <p>Real-time consumer scan preview</p>
      </div>
    </div>
    <div class="card-body">
      <div class="phone-shell">
        <div class="phone-screen">
          <div class="phone-status">
            <span>9:41</span>
            <span>● ●●</span>
          </div>
          <div class="phone-header">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Personalized health alerts
          </div>
          <div class="phone-body">
            <div class="phone-product">
              <div class="thumb" style="width:42px;height:50px;border-radius:7px">LY</div>
              <div>
                <strong style="font-size:10px;display:block">Lay’s Cream & Onion</strong>
                <span style="font-size:8px;color:var(--ink-500)">Snack · Potato Chips</span>
              </div>
            </div>

            <div id="phoneAlertBox" class="health-preview {{ strtolower($previewAlert?->severity ?? 'red') }}">
              <div id="phoneAlertCondition" class="prelabel">{{ $previewAlert?->condition ?? 'Diabetes' }}</div>
              <strong id="phoneAlertTitle" style="font-size:9px;display:block">{{ $previewAlert?->title ?? 'Better to avoid' }}</strong>
              <p id="phoneAlertMessage" style="font-size:8px;color:var(--ink-600);margin:3px 0 0">{{ $previewAlert?->message ?? 'Contains high sugar / added sugar.' }}</p>
            </div>

            <div class="health-preview yellow">
              <div class="prelabel">Recommendation</div>
              <strong style="font-size:9px;display:block">ScanWell Suggests</strong>
              <p id="phoneAlertRecommendation" style="font-size:8px;color:var(--ink-600);margin:3px 0 0">{{ $previewAlert?->recommendation ?? 'Choose an option with less added sugar or compare lower-sodium alternatives.' }}</p>
            </div>

            <div class="notice" style="margin-top:14px">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
              Health choices stay private on device. Never shared with advertisers.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function previewMobileAlert(condition, severity, title, message, recommendation) {
    const tone = severity.toLowerCase() === 'red' ? 'red' : (severity.toLowerCase() === 'yellow' ? 'yellow' : 'green');
    const box = document.getElementById('phoneAlertBox');
    box.className = `health-preview ${tone}`;
    document.getElementById('phoneAlertCondition').textContent = condition;
    document.getElementById('phoneAlertTitle').textContent = title;
    document.getElementById('phoneAlertMessage').textContent = message;
    document.getElementById('phoneAlertRecommendation').textContent = recommendation || 'Review nutritional breakdown before consuming.';
    showToast(`Phone preview updated for ${condition}.`, 'success', 'Simulator Updated');
  }
</script>
@endpush
@endsection
