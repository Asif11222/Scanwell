@extends('layouts.admin')

@section('title', 'ScanWell Admin - Duplicate Products')
@section('breadcrumb', 'Duplicate Products')
@section('page_title', 'Duplicate Products')

@section('content')
<div class="page-head">
  <div>
    <h2>Duplicate Products</h2>
    <p>Detect and resolve identical or overlapping product records across barcodes, brands, and titles</p>
  </div>
</div>

<div class="visual-kpi-grid">
  <div class="visual-kpi purple">
    <div class="visual-kpi-top">
      <div class="visual-kpi-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M15 9V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h4"/></svg>
      </div>
    </div>
    <div class="visual-kpi-value">{{ $potentialCount }}</div>
    <div class="visual-kpi-label">Potential Matches</div>
  </div>

  <div class="visual-kpi amber">
    <div class="visual-kpi-top">
      <div class="visual-kpi-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
      </div>
    </div>
    <div class="visual-kpi-value">{{ $highMatchCount }}</div>
    <div class="visual-kpi-label">High Match (≥90%)</div>
  </div>

  <div class="visual-kpi blue">
    <div class="visual-kpi-top">
      <div class="visual-kpi-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12z"/><circle cx="12" cy="12" r="3"/></svg>
      </div>
    </div>
    <div class="visual-kpi-value">{{ $reviewCount }}</div>
    <div class="visual-kpi-label">Under Review</div>
  </div>

  <div class="visual-kpi">
    <div class="visual-kpi-top">
      <div class="visual-kpi-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l4 4L19 6"/></svg>
      </div>
    </div>
    <div class="visual-kpi-value">{{ $resolvedCount }}</div>
    <div class="visual-kpi-label">Resolved</div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="compact-list">
      @forelse($duplicates as $d)
        <div class="compact-row">
          <div class="compact-row-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M15 9V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h4"/></svg>
          </div>
          <div class="compact-row-main">
            <strong>{{ $d->name }}</strong>
            <span>{{ $d->id }} · {{ $d->reason }}</span>
          </div>
          <span class="compact-score" style="color: {{ $d->match_percentage >= 90 ? 'var(--danger-strong)' : 'var(--warning-strong)' }}">
            {{ $d->match_percentage }}% match
          </span>
          <div class="actions">
            @php $st = strtolower(str_replace(' ', '-', $d->status)); @endphp
            <span class="badge {{ $st }}">{{ $d->status }}</span>
            @if(strtolower($d->status) !== 'resolved')
              <button type="button" class="btn btn-secondary btn-sm" onclick="openDuplicateModal('{{ $d->id }}', '{{ $d->name }}', {{ $d->match_percentage }}, '{{ $d->reason }}')">
                Resolve
              </button>
            @endif
          </div>
        </div>
      @empty
        <div style="padding:28px;text-align:center;color:var(--ink-500)">No potential duplicate records detected.</div>
      @endforelse
    </div>
  </div>
</div>

@push('scripts')
<script>
  function openDuplicateModal(id, name, match, reason) {
    openModal({
      title: `Resolve Duplicate Check: ${id}`,
      subtitle: `${name} (${match}% match)`,
      body: `
        <div class="info-strip warning">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <div class="copy">
            <strong>Match Reason: ${reason}</strong>
            <span>Comparing records with high barcode/nutrition similarity. Choose whether to merge into canonical product or mark as distinct.</span>
          </div>
        </div>
        <p style="font-size:12px;color:var(--ink-700)">Select resolution action:</p>
      `,
      footer: `
        <form method="POST" action="{{ url('admin/duplicate-products') }}/${id}/resolve" style="display:flex;gap:8px">
          @csrf
          <button type="submit" name="action" value="Resolved" class="btn btn-secondary">Mark Distinct Product</button>
          <button type="submit" name="action" value="Resolved" class="btn btn-primary">Merge into Primary Product</button>
        </form>
      `
    });
  }
</script>
@endpush
@endsection
