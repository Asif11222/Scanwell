@extends('layouts.admin')

@section('title', 'ScanWell Admin - Product Corrections')
@section('breadcrumb', 'Product Corrections')
@section('page_title', 'Product Corrections')

@section('content')
<div class="page-head">
  <div>
    <h2>Product Corrections</h2>
    <p>Review community-proposed modifications to nutrition, ingredients, and package metadata</p>
  </div>
</div>

<div class="visual-kpi-grid">
  <div class="visual-kpi amber">
    <div class="visual-kpi-top">
      <div class="visual-kpi-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
      </div>
    </div>
    <div class="visual-kpi-value">{{ $pending }}</div>
    <div class="visual-kpi-label">Pending Decisions</div>
  </div>

  <div class="visual-kpi blue">
    <div class="visual-kpi-top">
      <div class="visual-kpi-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12z"/><circle cx="12" cy="12" r="3"/></svg>
      </div>
    </div>
    <div class="visual-kpi-value">{{ $review }}</div>
    <div class="visual-kpi-label">In Review</div>
  </div>

  <div class="visual-kpi">
    <div class="visual-kpi-top">
      <div class="visual-kpi-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l4 4L19 6"/></svg>
      </div>
    </div>
    <div class="visual-kpi-value">{{ $approved }}</div>
    <div class="visual-kpi-label">Approved to Date</div>
  </div>

  <div class="visual-kpi amber">
    <div class="visual-kpi-top">
      <div class="visual-kpi-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
      </div>
    </div>
    <div class="visual-kpi-value">{{ $healthImpacting }}</div>
    <div class="visual-kpi-label">Health-Impacting</div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="compact-list">
      @forelse($corrections as $c)
        <div class="compact-row">
          <div class="compact-row-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              @if($c->risk === 'Health-impacting')
                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              @else
                <path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/>
              @endif
            </svg>
          </div>
          <div class="compact-row-main">
            <strong>{{ $c->product_name }} · {{ $c->field }}</strong>
            <span>{{ $c->id }} · By {{ $c->requested_by }} · {{ $c->submitted_at ? $c->submitted_at->format('M d, H:i') : $c->created_at->format('M d, H:i') }}</span>
          </div>
          <span class="compact-score" style="color: {{ $c->risk === 'Health-impacting' ? 'var(--danger-strong)' : 'var(--ink-600)' }}">
            {{ $c->risk }}
          </span>
          <div class="actions">
            @php $st = strtolower($c->status); @endphp
            <span class="badge {{ $st }}" style="min-width:84px;width:84px;justify-content:center;text-align:center">{{ $c->status }}</span>
            @if($c->status === 'Review')
              <button type="button" class="btn btn-secondary btn-sm" style="min-width:64px;justify-content:center;text-align:center" title="Action" onclick="openCorrectionReview('{{ $c->id }}')">
                Action
              </button>
            @else
              <button type="button" class="btn btn-secondary btn-sm disabled" disabled style="min-width:64px;justify-content:center;text-align:center" title="Action neutralized ({{ $c->status }})">
                Action
              </button>
            @endif
          </div>
        </div>
      @empty
        <div style="padding:28px;text-align:center;color:var(--ink-500)">No product corrections found.</div>
      @endforelse
    </div>
  </div>
</div>

@push('scripts')
<script>
  function openCorrectionReview(id) {
    fetch(`{{ url('admin/product-corrections') }}/${id}`)
      .then(res => res.json())
      .then(c => {
        if (c.status !== 'Review') {
          if (typeof showToast === 'function') {
            showToast(`Correction ${c.id} is ${c.status} and cannot be modified.`, 'warning', 'Action Neutralized');
          }
          return;
        }

        openModal({
          title: `Correction Request ${c.id}`,
          subtitle: `${c.product_name} · Field: ${c.field}`,
          size: 'wide',
          body: `
            <div class="workflow-strip" style="grid-template-columns:repeat(3,1fr)">
              <div class="workflow-step">
                <div class="step-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 13h6M9 17h6M9 9h2"/></svg></div>
                <strong>Current Value</strong>
                <span style="font-size:12px">${c.from_value || 'None'}</span>
              </div>
              <div class="workflow-step active">
                <div class="step-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/></svg></div>
                <strong>Proposed Value</strong>
                <span style="font-size:12px;color:var(--green-700)">${c.to_value || 'None'}</span>
              </div>
              <div class="workflow-step">
                <div class="step-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></div>
                <strong>Verification Source</strong>
                <span style="font-size:12px">${c.source || 'User submission'}</span>
              </div>
            </div>

            <div class="info-strip ${c.risk === 'Health-impacting' ? 'warning' : ''}">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              <div class="copy">
                <strong>Risk Category: ${c.risk}</strong>
                <span>Requested by ${c.requested_by}. Approving will update this field directly on product ID #${c.product_id}.</span>
              </div>
            </div>
          `,
          footer: `
            <form method="POST" action="{{ url('admin/product-corrections') }}/${c.id}/review" style="display:flex;gap:8px;width:100%;align-items:center;justify-content:flex-end">
              @csrf
              <button type="button" class="btn btn-danger" onclick="deleteCorrection('${c.id}', '${(c.product_name || '').replace(/'/g, "\\'")}')" style="margin-right:auto">Delete</button>
              <button type="submit" name="status" value="Rejected" class="btn btn-secondary">Reject</button>
              <button type="submit" name="status" value="Review" class="btn btn-secondary">Flag for Review</button>
              <button type="submit" name="status" value="Approved" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l4 4L19 6"/></svg>
                Approve & Update Product
              </button>
            </form>
          `
        });
      });
  }

  function deleteCorrection(id, productName) {
    if (!confirm(`Are you sure you want to permanently delete correction ${id} for ${productName}?`)) {
      return;
    }
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch(`{{ url('admin/product-corrections') }}/${id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json',
      }
    })
    .then(res => res.json())
    .then(data => {
      closeModal();
      window.location.reload();
    })
    .catch(err => {
      alert('Failed to delete correction.');
    });
  }
</script>
@endpush
@endsection
