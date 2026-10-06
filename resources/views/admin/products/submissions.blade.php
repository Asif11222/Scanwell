@extends('layouts.admin')

@section('title', 'ScanWell Admin - OCR / Product Review')
@section('breadcrumb', 'OCR / Product Review')
@section('page_title', 'OCR / Product Review')

@section('content')
<div class="page-head">
  <div>
    <h2>OCR / Product Review</h2>
    <p>Review incoming packaging extractions from mobile scanning before approving into catalog</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.submissions.index') }}" class="btn btn-secondary">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v6h6"/><path d="M5.5 15a8 8 0 1 0 2-8.5L4 10"/></svg>
      Refresh Queue
    </a>
  </div>
</div>

<div class="info-strip">
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
  <div class="copy">
    <strong>Human review stays in control.</strong>
    <span>Low-confidence OCR fields, duplicates, and health-related data should be verified before approving for public catalog view.</span>
  </div>
</div>

<div class="card">
  <!-- Toolbar Filter -->
  <form method="GET" action="{{ route('admin.submissions.index') }}" class="toolbar">
    <select name="status" class="filter-select" onchange="this.form.submit()">
      <option value="All" {{ $status === 'All' ? 'selected' : '' }}>All Statuses</option>
      <option value="Pending" {{ $status === 'Pending' ? 'selected' : '' }}>Pending</option>
      <option value="Review" {{ $status === 'Review' ? 'selected' : '' }}>Review</option>
      <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Approved</option>
      <option value="Rejected" {{ $status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
    </select>
    <div class="toolbar-spacer"></div>
    <span class="result-count">{{ $submissions->count() }} submission{{ $submissions->count() === 1 ? '' : 's' }}</span>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Submission</th>
          <th>Contributor</th>
          <th>OCR Confidence</th>
          <th>Duplicate Check</th>
          <th>Low-Confidence Fields</th>
          <th>Status</th>
          <th>Submitted</th>
          <th style="text-align:right">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($submissions as $s)
          <tr>
            <td>
              <div class="product-cell">
                <div class="thumb">{{ strtoupper(substr($s->brand, 0, 2)) }}</div>
                <div>
                  <div class="cell-main">{{ $s->product_name }}</div>
                  <div class="cell-sub">{{ $s->id }} · {{ $s->barcode }}</div>
                </div>
              </div>
            </td>
            <td>{{ $s->contributor }}</td>
            <td>
              <div class="metric-row">
                <div class="confidence {{ $s->confidence < 90 ? 'low' : '' }}">
                  <span style="width: {{ $s->confidence }}%"></span>
                </div>
                <span class="confidence-value">{{ $s->confidence }}%</span>
              </div>
            </td>
            <td>
              @if(str_contains($s->duplicate_check ?? '', '%'))
                <span class="badge scheduled">{{ $s->duplicate_check }}</span>
              @elseif($s->duplicate_check === 'None' || str_contains($s->duplicate_check ?? '', 'No close'))
                <span class="badge verified">{{ $s->duplicate_check }}</span>
              @else
                <span class="badge pending">{{ $s->duplicate_check }}</span>
              @endif
            </td>
            <td>
              @if(!empty($s->low_fields) && count($s->low_fields) > 0)
                <span class="badge pending">{{ count($s->low_fields) }} field{{ count($s->low_fields) > 1 ? 's' : '' }}</span>
              @else
                <span style="color:var(--ink-400)">—</span>
              @endif
            </td>
            <td>
              @php $st = strtolower($s->status); @endphp
              <span class="badge {{ $st }}" style="min-width:84px;width:84px;justify-content:center;text-align:center">{{ $s->status }}</span>
            </td>
            <td>{{ $s->submitted_at ? $s->submitted_at->diffForHumans() : $s->created_at->format('M d, H:i') }}</td>
            <td>
              <div class="actions">
                @if($s->status === 'Review' || $s->status === 'Pending')
                  <button type="button" class="btn btn-secondary btn-sm" style="min-width:64px;justify-content:center;text-align:center" onclick="openSubmissionReview('{{ $s->id }}')">
                    Review
                  </button>
                @else
                  <button type="button" class="btn btn-secondary btn-sm disabled" disabled style="min-width:64px;justify-content:center;text-align:center" title="Action neutralized ({{ $s->status }})">
                    Review
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="padding:32px;text-align:center;color:var(--ink-400)">
              No submissions found in this status queue.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
  function openSubmissionReview(id) {
    fetch(`{{ url('admin/submissions') }}/${id}`)
      .then(res => res.json())
      .then(s => {
        if (s.status === 'Approved' || s.status === 'Rejected') {
          if (typeof showToast === 'function') {
            showToast(`Submission ${s.id} is already ${s.status} and cannot be modified.`, 'warning', 'Action Neutralized');
          }
          return;
        }

        const lowFields = s.low_fields || [];
        const extracted = s.extracted_fields || {};

        const fields = [
          ['Product Name', s.product_name, false],
          ['Brand', s.brand, false],
          ['Barcode', s.barcode, false],
          ['Category', extracted['Category'] || 'Breakfast Cereals', lowFields.includes('Category')],
          ['Serving Size', extracted['Serving'] || '40 g', lowFields.includes('Serving size')],
          ['Calories', extracted['Calories'] || '190 kcal', lowFields.includes('Calories')],
          ['Sodium', extracted['Sodium'] || '210 mg', lowFields.includes('Sodium')],
          ['Sugar', extracted['Sugar'] || '8 g', lowFields.includes('Sugar')]
        ];

        openModal({
          title: `Review ${s.id}`,
          subtitle: `Submitted by ${s.contributor} · OCR Confidence ${s.confidence}%`,
          size: 'wide',
          body: `
            <div class="info-strip ${s.confidence < 90 ? 'warning' : ''}">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V5a1 1 0 0 1 1-1h2M17 4h2a1 1 0 0 1 1 1v2M20 17v2a1 1 0 0 1-1 1h-2M7 20H5a1 1 0 0 1-1-1v-2"/></svg>
              <div class="copy">
                <strong>OCR Extraction Confidence: ${s.confidence}%</strong>
                <span>${lowFields.length ? `${lowFields.length} extracted fields require closer inspection before approval.` : 'Confidence is high; confirm package details.'}</span>
              </div>
            </div>
            <div class="modal-split">
              <div>
                <div class="section-label">Extracted Fields</div>
                <div class="form-grid">
                  ${fields.map(f => `
                    <div class="field">
                      <label>${f[0]} ${f[2] ? '<span style="color:var(--warning-strong)">· low confidence</span>' : ''}</label>
                      <input class="input" value="${f[1]}" style="${f[2] ? 'border-color:var(--warning-border);background:var(--warning-bg)' : ''}">
                    </div>
                  `).join('')}
                </div>
                <div class="section-label">Duplicate Check Result</div>
                <div class="notice">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                  ${s.duplicate_check}. Admin can compare, merge, or publish as a new verified product.
                </div>
              </div>
              <div>
                <div class="section-label">Submitted Package Photo</div>
                <div style="height:260px;border-radius:14px;background:linear-gradient(145deg,#f0f7f2,#dbeade);border:1px solid var(--line);display:grid;place-items:center;color:var(--green-800);text-align:center">
                  <div>
                    <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="margin:0 auto 8px"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                    <strong style="display:block;font-size:12px">Package Label Photo</strong>
                    <span style="font-size:10px;color:var(--ink-500)">Captured via Mobile App Camera</span>
                  </div>
                </div>
                <div class="tag-row" style="margin-top:12px">
                  <span class="tag">Barcode</span>
                  <span class="tag">Front Pack</span>
                  <span class="tag">Nutrition Facts</span>
                  <span class="tag">Ingredients</span>
                </div>
              </div>
            </div>
          `,
          footer: `
            <form method="POST" action="{{ url('admin/submissions') }}/${s.id}/review" style="display:flex;gap:8px;width:100%;justify-content:flex-end">
              @csrf
              <button type="submit" name="status" value="Rejected" class="btn btn-secondary">Reject</button>
              <button type="submit" name="status" value="Review" class="btn btn-secondary">Request Correction</button>
              <button type="submit" name="status" value="Approved" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l4 4L19 6"/></svg>
                Approve to Catalog
              </button>
            </form>
          `
        });
      });
  }
</script>
@endpush
@endsection
