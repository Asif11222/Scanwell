@extends('layouts.admin')

@section('title', 'ScanWell Admin - Pending Corrections')
@section('breadcrumb', 'Pending Corrections')
@section('page_title', 'Pending Corrections')

@section('content')
<div class="page-head">
  <div>
    <h2>Pending Corrections</h2>
    <p>Product modifications currently awaiting review and decision by admin content editors</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.corrections.index') }}" class="btn btn-secondary">All Corrections</a>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="compact-list">
      @forelse($corrections as $c)
        <div class="compact-row">
          <div class="compact-row-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
            </svg>
          </div>
          <div class="compact-row-main">
            <strong>{{ $c->product_name }} · {{ $c->field }}</strong>
            <span>{{ $c->id }} · {{ $c->requested_by }} · {{ $c->submitted_at ? $c->submitted_at->format('M d, H:i') : $c->created_at->format('M d, H:i') }}</span>
          </div>
          <span class="compact-score" style="color: {{ $c->risk === 'Health-impacting' ? 'var(--danger-strong)' : 'var(--ink-600)' }}">
            {{ $c->risk }}
          </span>
          <div class="actions">
            <span class="badge pending" style="min-width:84px;width:84px;justify-content:center;text-align:center">Pending</span>
            <button type="button" class="btn btn-secondary btn-sm" style="min-width:64px;justify-content:center;text-align:center" onclick="openReviewModal('{{ $c->id }}')">Review</button>
          </div>
        </div>
      @empty
        <div style="padding:32px;text-align:center;color:var(--ink-400)">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin:0 auto 8px;color:var(--green-700)"><path d="M5 12l4 4L19 6"/></svg>
          <strong style="display:block;color:var(--ink-800)">All Caught Up!</strong>
          <span style="font-size:11px">There are no pending corrections waiting for review.</span>
        </div>
      @endforelse
    </div>
  </div>
</div>

@push('scripts')
<script>
  function openReviewModal(id) {
    fetch(`{{ url('admin/product-corrections') }}/${id}`)
      .then(res => res.json())
      .then(c => {
        openModal({
          title: `Review Correction ${c.id}`,
          subtitle: `${c.product_name} (${c.field})`,
          size: 'wide',
          body: `
            <div class="grid grid-2" style="margin-bottom:14px">
              <div class="stat-card">
                <div class="stat-label">Current Value</div>
                <div style="font-size:13px;font-weight:700;margin-top:4px">${c.from_value || 'None'}</div>
              </div>
              <div class="stat-card" style="border-color:#b8d8c4;background:var(--green-50)">
                <div class="stat-label" style="color:var(--green-900)">Proposed Value</div>
                <div style="font-size:13px;font-weight:700;margin-top:4px;color:var(--green-800)">${c.to_value || 'None'}</div>
              </div>
            </div>
            <div class="notice">
              Source: ${c.source || 'Submitted package photo'}. Requested by: ${c.requested_by}.
            </div>
          `,
          footer: `
            <form method="POST" action="{{ url('admin/product-corrections') }}/${c.id}/review" style="display:flex;gap:8px">
              @csrf
              <button type="submit" name="status" value="Rejected" class="btn btn-secondary">Reject</button>
              <button type="submit" name="status" value="Approved" class="btn btn-primary">Approve & Patch Product</button>
            </form>
          `
        });
      });
  }
</script>
@endpush
@endsection
