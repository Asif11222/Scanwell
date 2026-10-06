@extends('layouts.admin')

@section('title', 'ScanWell Admin - Notifications')
@section('breadcrumb', 'Notifications')
@section('page_title', 'Notifications')

@section('content')
<div class="page-head">
  <div>
    <h2>Push Notifications</h2>
    <p>Schedule and broadcast targeted mobile push alerts with audience filtering and in-app deep links</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn-primary" onclick="openCreateNotificationModal()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
      New Notification
    </button>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Notification</th>
          <th>Audience Segment</th>
          <th>Schedule</th>
          <th>Deep Link</th>
          <th>Status</th>
          <th>Sent</th>
          <th>Opened</th>
          <th>Open Rate</th>
        </tr>
      </thead>
      <tbody>
        @forelse($notifications as $n)
          <tr>
            <td>
              <div class="cell-main">{{ $n->title }}</div>
              <div class="cell-sub" style="max-width:280px">{{ $n->body }}</div>
            </td>
            <td><strong>{{ $n->audience }}</strong></td>
            <td><span class="cell-sub">{{ $n->schedule_time ?? 'Immediate' }}</span></td>
            <td><code>{{ $n->deep_link }}</code></td>
            <td>
              @php $st = strtolower($n->status); @endphp
              <span class="badge {{ $st }}">{{ $n->status }}</span>
            </td>
            <td>{{ number_format($n->sent_count) }}</td>
            <td>{{ number_format($n->opened_count) }}</td>
            <td><strong>{{ $n->open_rate }}%</strong></td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="padding:32px;text-align:center;color:var(--ink-400)">No notification broadcasts created yet.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
  function openCreateNotificationModal() {
    openModal({
      title: 'Schedule Push Notification',
      subtitle: 'Target specific user groups and navigate them to saved products or health profile',
      body: `
        <form id="createNotifForm" method="POST" action="{{ route('admin.notifications.store') }}">
          @csrf
          <div class="form-grid">
            <div class="field full">
              <label>Notification Title *</label>
              <input name="title" class="input" required placeholder="e.g. Your saved product was updated">
            </div>
            <div class="field full">
              <label>Push Message Body *</label>
              <textarea name="body" class="textarea" required placeholder="Enter message text displayed on phone lock screen..."></textarea>
            </div>
            <div class="field">
              <label>Target Audience *</label>
              <select name="audience" class="select" required>
                <option value="All users">All Active Users</option>
                <option value="Active users (30 days)" selected>Active users (Last 30 days)</option>
                <option value="Profiles without health concerns">Profiles Without Health Concerns</option>
                <option value="Contributors">Verified Contributors</option>
              </select>
            </div>
            <div class="field">
              <label>Status *</label>
              <select name="status" class="select" required>
                <option value="Scheduled" selected>Scheduled</option>
                <option value="Draft">Draft</option>
              </select>
            </div>
            <div class="field">
              <label>Schedule Date & Time</label>
              <input name="schedule_time" class="input" placeholder="e.g. Sep 20, 18:00">
            </div>
            <div class="field">
              <label>In-App Destination Deep Link *</label>
              <input name="deep_link" class="input" value="scanwell://home" required>
            </div>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('createNotifForm').submit()">Save & Schedule</button>
      `
    });
  }
</script>
@endpush
@endsection
