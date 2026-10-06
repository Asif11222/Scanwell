@extends('layouts.admin')

@section('title', 'ScanWell Admin - Verification Queue')
@section('breadcrumb', 'Verification Queue')
@section('page_title', 'Verification Queue')

@section('content')
<div class="page-head">
  <div>
    <h2>Verification Queue</h2>
    <p>Catalog records awaiting human source verification against official packaging labels</p>
  </div>
</div>

<div class="workflow-strip">
  <div class="workflow-step active">
    <div class="step-icon">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
    </div>
    <strong>Waiting</strong>
    <span>{{ $products->count() }}</span>
  </div>
  <div class="workflow-step">
    <div class="step-icon">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V5a1 1 0 0 1 1-1h2M17 4h2a1 1 0 0 1 1 1v2M20 17v2a1 1 0 0 1-1 1h-2M7 20H5a1 1 0 0 1-1-1v-2"/></svg>
    </div>
    <strong>OCR Sourced</strong>
    <span>{{ $ocrSourcedCount }}</span>
  </div>
  <div class="workflow-step">
    <div class="step-icon">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    </div>
    <strong>Flagged</strong>
    <span>{{ $flaggedCount }}</span>
  </div>
  <div class="workflow-step">
    <div class="step-icon">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
    </div>
    <strong>Verified</strong>
    <span>{{ $verifiedCount }}</span>
  </div>
  <div class="workflow-step">
    <div class="step-icon">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8l-9 5-9-5 9-5 9 5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
    </div>
    <strong>Total Catalog</strong>
    <span>{{ $totalCount }}</span>
  </div>
</div>

<div class="product-visual-grid">
  @forelse($products as $p)
    <article class="product-visual-card" style="display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <div class="product-visual-hero" style="background:linear-gradient(145deg,var(--warning-bg),#fff5df);color:var(--warning-strong);cursor:pointer;" onclick="previewProduct({{ $p->id }})" title="Click to view details">
          @if(!empty($p->image_url))
            <img src="{{ str_starts_with($p->image_url, 'http') ? $p->image_url : asset($p->image_url) }}" alt="{{ $p->name }}" loading="lazy" onerror="this.style.display='none';">
          @else
            <div class="product-glyph">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            </div>
          @endif
          <div class="corner-badge">
            <span class="badge pending">Awaiting Verification</span>
          </div>
        </div>
        <div class="product-visual-body" style="padding-bottom:10px;">
          <h4 style="cursor:pointer;" onclick="previewProduct({{ $p->id }})" title="Click to view details">{{ $p->name }}</h4>
          <p>{{ $p->brand }} · {{ $p->barcode }}</p>
          <div class="product-visual-meta">
            <span class="cell-sub">Source: {{ $p->source ?? 'Contributor intake' }}</span>
            <span class="severity {{ $p->flags_count >= 3 ? 'red' : ($p->flags_count === 2 ? 'yellow' : 'green') }}">
              <i></i>
              {{ $p->flags_count }} flag{{ $p->flags_count === 1 ? '' : 's' }}
            </span>
          </div>
        </div>
      </div>
      <div style="padding:10px 13px 13px;display:flex;flex-wrap:wrap;gap:6px;border-top:1px solid var(--line);background:var(--surface);">
        <button type="button" class="btn btn-secondary btn-sm" style="flex:1;min-width:80px;justify-content:center;height:32px;font-size:11.5px;gap:4px;" onclick="previewProduct({{ $p->id }})">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          View
        </button>
        <button type="button" class="btn btn-secondary btn-sm" style="flex:1;min-width:70px;justify-content:center;height:32px;font-size:11.5px;gap:4px;" onclick="editProduct({{ $p->id }})">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </button>
        <form method="POST" action="{{ route('admin.products.verify', $p) }}" style="flex:1.2;min-width:115px;margin:0">
          @csrf
          <button type="submit" class="btn btn-sm" style="width:100%;justify-content:center;height:32px;font-size:11.5px;gap:4px;background:#059669;color:#fff;border-color:#059669" title="Verify product and publish immediately">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
            Verify &amp; Publish
          </button>
        </form>
      </div>
    </article>
  @empty
    <div style="grid-column:1/-1;padding:36px;text-align:center;background:#fff;border-radius:14px;border:1px solid var(--line)">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--green-700);margin:0 auto 8px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
      <strong style="display:block;color:var(--ink-900)">Verification Queue Clear</strong>
      <span style="font-size:11px;color:var(--ink-500)">All catalog items currently have verified source nutrition labels.</span>
    </div>
  @endforelse
</div>

@push('scripts')
@include('admin.products._product_scripts')
@endpush
@endsection
