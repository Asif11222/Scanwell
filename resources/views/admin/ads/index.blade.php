@extends('layouts.admin')

@section('title', 'ScanWell Admin - Ads & Promotions')
@section('breadcrumb', 'Ads & Promotions')
@section('page_title', 'Ads & Promotions')

@section('content')
<div class="page-head">
  <div>
    <h2>Ads & Promotions</h2>
    <p>Create, schedule, target, and measure clearly labeled sponsored campaigns without changing Flutter mobile code</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn-primary btn-ad" onclick="openCreateCampaignModal()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
      Create Campaign
    </button>
  </div>
</div>

<div class="info-strip ad-info">
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
  <div class="copy">
    <strong>Advertising is strictly segregated from health intelligence.</strong>
    <span>Every creative is automatically labeled "Sponsored" or "Ad". Campaigns can never suppress health warnings or receive private consumer health profiles.</span>
  </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-4" style="margin-bottom:18px">
  <div class="stat-card">
    <div class="stat-top">
      <div class="stat-icon purple">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11v2a2 2 0 0 0 2 2h2l4 4V5L7 9H5a2 2 0 0 0-2 2zM11 8l8-3v14l-8-3M7 15v4"/></svg>
      </div>
      <span class="delta flat">Live Set</span>
    </div>
    <div class="stat-value">{{ $activeCampaigns->count() }}</div>
    <div class="stat-label">Active Campaigns</div>
  </div>

  <div class="stat-card">
    <div class="stat-top">
      <div class="stat-icon blue">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12z"/><circle cx="12" cy="12" r="3"/></svg>
      </div>
      <span class="delta up">+4.1%</span>
    </div>
    <div class="stat-value">{{ number_format($totalImpressions) }}</div>
    <div class="stat-label">Impressions</div>
  </div>

  <div class="stat-card">
    <div class="stat-top">
      <div class="stat-icon">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19l5-6 4 3 7-11"/><path d="M17 5h3v3"/></svg>
      </div>
      <span class="delta up">Engagements</span>
    </div>
    <div class="stat-value">{{ number_format($totalClicks) }}</div>
    <div class="stat-label">Total Clicks</div>
  </div>

  <div class="stat-card">
    <div class="stat-top">
      <div class="stat-icon purple">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h4l2-5 4 10 2-5h6"/></svg>
      </div>
      <span class="delta up">{{ $avgCtr }}% CTR</span>
    </div>
    <div class="stat-value">{{ $avgCtr }}%</div>
    <div class="stat-label">Average CTR</div>
  </div>
</div>

<div class="card">
  <form method="GET" action="{{ route('admin.ads.index') }}" class="toolbar">
    <select name="status" class="filter-select" onchange="this.form.submit()">
      <option value="All" {{ $status === 'All' ? 'selected' : '' }}>All Statuses</option>
      <option value="Active" {{ $status === 'Active' ? 'selected' : '' }}>Active</option>
      <option value="Scheduled" {{ $status === 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
      <option value="Paused" {{ $status === 'Paused' ? 'selected' : '' }}>Paused</option>
      <option value="Draft" {{ $status === 'Draft' ? 'selected' : '' }}>Draft</option>
      <option value="Archived" {{ $status === 'Archived' ? 'selected' : '' }}>Archived</option>
    </select>
    <div class="toolbar-spacer"></div>
    <span class="result-count">{{ $campaigns->count() }} campaign{{ $campaigns->count() === 1 ? '' : 's' }}</span>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Campaign</th>
          <th>Placement</th>
          <th>Schedule</th>
          <th>Audience</th>
          <th>Impressions</th>
          <th>Clicks</th>
          <th>CTR</th>
          <th>Status</th>
          <th>Live Toggle</th>
          <th style="text-align:right">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($campaigns as $c)
          <tr>
            <td>
              <div class="cell-main">{{ $c->name }}</div>
              <div class="cell-sub"><span class="badge ad">Sponsored</span> {{ $c->type }}</div>
            </td>
            <td><strong>{{ $c->placement }}</strong></td>
            <td>
              <div class="cell-main">{{ $c->start_at->format('M d, Y') }}</div>
              <div class="cell-sub">to {{ $c->end_at->format('M d, Y') }}</div>
            </td>
            <td>
              <div class="cell-main">{{ $c->segment }}</div>
              <div class="cell-sub">{{ $c->region }}</div>
            </td>
            <td>{{ number_format($c->impressions) }}</td>
            <td>{{ number_format($c->clicks) }}</td>
            <td><strong>{{ $c->ctr }}%</strong></td>
            <td>
              @php $st = strtolower($c->status); @endphp
              <span class="badge {{ $st }}">{{ $c->status }}</span>
            </td>
            <td>
              <button type="button" class="toggle {{ $c->status === 'Active' ? 'on ad-on' : '' }}" onclick="toggleCampaign({{ $c->id }})" title="Toggle Active / Paused"></button>
            </td>
            <td>
              <div class="actions">
                <button type="button" class="mini-btn" title="Edit Campaign" onclick="openEditCampaignModal({{ $c->id }})">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/></svg>
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" style="padding:32px;text-align:center;color:var(--ink-400)">No campaigns found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
  function toggleCampaign(id) {
    fetch(`{{ url('admin/ads') }}/${id}/toggle`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      }
    })
    .then(res => res.json())
    .then(data => {
      showToast(data.message, 'success', 'Campaign Status');
      setTimeout(() => location.reload(), 600);
    });
  }

  function openCreateCampaignModal() {
    openModal({
      title: 'Create Sponsored Campaign',
      subtitle: 'Custom validation will enforce ScanWell medical ad integrity policies',
      size: 'wide',
      body: `
        <form id="createCampaignForm" method="POST" action="{{ route('admin.ads.store') }}">
          @csrf
          <div class="form-grid">
            <div class="field full">
              <label>Campaign Name *</label>
              <input name="name" class="input" required placeholder="e.g. Healthy Breakfast Week">
            </div>
            <div class="field">
              <label>Campaign Format *</label>
              <select name="type" class="select" required>
                <option value="Banner">Banner</option>
                <option value="Card" selected>Card</option>
                <option value="Sponsored collection">Sponsored Collection</option>
                <option value="Full-screen">Full-screen Promotion</option>
              </select>
            </div>
            <div class="field">
              <label>Placement Zone *</label>
              <select name="placement" class="select" required>
                <option value="Home Banner">Home Banner</option>
                <option value="Home Feed" selected>Home Feed</option>
                <option value="Search Results">Search Results</option>
                <option value="Product Details">Product Details</option>
                <option value="Scan Result">Scan Result</option>
                <option value="Personalized Alerts">Personalized Alerts</option>
                <option value="Full-screen Promotion">Full-screen Promotion</option>
              </select>
            </div>
            <div class="field full">
              <label>Headline *</label>
              <input name="headline" class="input" required placeholder="e.g. Start your morning with smarter choices">
            </div>
            <div class="field full">
              <label>Promotional Copy *</label>
              <textarea name="copy" class="textarea" required placeholder="Describe product benefits without violating deceptive health policies..."></textarea>
            </div>
            <div class="field">
              <label>Call to Action (CTA) *</label>
              <input name="cta" class="input" value="Explore Products" required>
            </div>
            <div class="field">
              <label>Deep Link / Action URL *</label>
              <input name="cta_url" class="input" value="scanwell://search" required>
            </div>
            <div class="field">
              <label>Start Date/Time *</label>
              <input name="start_at" type="datetime-local" class="input" value="{{ now()->format('Y-m-d\TH:i') }}" required>
            </div>
            <div class="field">
              <label>End Date/Time *</label>
              <input name="end_at" type="datetime-local" class="input" value="{{ now()->addDays(7)->format('Y-m-d\TH:i') }}" required>
            </div>
            <div class="field">
              <label>Status *</label>
              <select name="status" class="select" required>
                <option value="Active" selected>Active</option>
                <option value="Scheduled">Scheduled</option>
                <option value="Paused">Paused</option>
                <option value="Draft">Draft</option>
              </select>
            </div>
            <div class="field">
              <label>Priority (1 - 100) *</label>
              <input name="priority" type="number" min="1" max="100" class="input" value="50" required>
            </div>
            <div class="field">
              <label>Frequency Cap (per user window)</label>
              <input name="frequency_cap" type="number" min="1" max="20" class="input" value="3" required>
            </div>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-primary btn-ad" onclick="document.getElementById('createCampaignForm').submit()">Save Campaign</button>
      `
    });
  }

  function openEditCampaignModal(id) {
    fetch(`{{ url('admin/ads') }}/${id}`)
      .then(res => res.json())
      .then(c => {
        openModal({
          title: `Edit Campaign: ${c.name}`,
          size: 'wide',
          body: `
            <form id="editCampaignForm" method="POST" action="{{ url('admin/ads') }}/${c.id}">
              @csrf
              @method('PUT')
              <div class="form-grid">
                <div class="field full">
                  <label>Campaign Name *</label>
                  <input name="name" class="input" value="${c.name || ''}" required>
                </div>
                <div class="field">
                  <label>Headline *</label>
                  <input name="headline" class="input" value="${c.headline || ''}" required>
                </div>
                <div class="field">
                  <label>Placement *</label>
                  <input name="placement" class="input" value="${c.placement || ''}" required>
                </div>
                <div class="field full">
                  <label>Copy *</label>
                  <textarea name="copy" class="textarea" required>${c.copy || ''}</textarea>
                </div>
                <div class="field">
                  <label>CTA Button *</label>
                  <input name="cta" class="input" value="${c.cta || ''}" required>
                </div>
                <div class="field">
                  <label>CTA URL *</label>
                  <input name="cta_url" class="input" value="${c.cta_url || ''}" required>
                </div>
                <div class="field">
                  <label>Status *</label>
                  <select name="status" class="select" required>
                    <option value="Active" ${c.status === 'Active' ? 'selected' : ''}>Active</option>
                    <option value="Scheduled" ${c.status === 'Scheduled' ? 'selected' : ''}>Scheduled</option>
                    <option value="Paused" ${c.status === 'Paused' ? 'selected' : ''}>Paused</option>
                    <option value="Draft" ${c.status === 'Draft' ? 'selected' : ''}>Draft</option>
                    <option value="Archived" ${c.status === 'Archived' ? 'selected' : ''}>Archived</option>
                  </select>
                </div>
                <div class="field">
                  <label>Priority</label>
                  <input name="priority" type="number" class="input" value="${c.priority || 50}">
                </div>
                <div class="field">
                  <label>Frequency Cap</label>
                  <input name="frequency_cap" type="number" class="input" value="${c.frequency_cap || 1}">
                </div>
              </div>
            </form>
          `,
          footer: `
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn btn-primary btn-ad" onclick="document.getElementById('editCampaignForm').submit()">Save Changes</button>
          `
        });
      });
  }
</script>
@endpush
@endsection
