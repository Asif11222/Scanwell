@extends('layouts.admin')

@section('title', 'ScanWell Admin - Product Details')
@section('breadcrumb', 'Product Details')
@section('page_title', 'Product Details')

@section('content')
<div class="page-head">
  <div>
    <h2>Product Details</h2>
    <p>Visual catalog explorer · card-based review of package images, health severity flags, and status</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      Table View
    </a>
  </div>
</div>

<style>
  .workflow-strip-filter {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 18px;
  }
  .workflow-step-btn {
    background: var(--surface);
    border: 1.5px solid var(--line);
    border-radius: 14px;
    padding: 14px 16px;
    text-align: center;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    transition: all .18s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    user-select: none;
    box-shadow: var(--shadow-sm);
  }
  .workflow-step-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    border-color: var(--green-500);
  }
  .workflow-step-btn .step-icon {
    width: 36px;
    height: 36px;
    border-radius: 11px;
    background: var(--surface-muted);
    display: grid;
    place-items: center;
    margin: 0 auto 8px;
    color: var(--ink-700);
    transition: all .18s ease;
  }
  .workflow-step-btn strong {
    display: block;
    font-size: 11.5px;
    color: var(--ink-700);
    letter-spacing: .2px;
    transition: color .18s ease;
  }
  .workflow-step-btn span {
    display: block;
    font-size: 19px;
    font-weight: 850;
    margin-top: 3px;
    color: var(--ink-950);
    transition: color .18s ease;
  }
  .workflow-step-btn.active {
    background: var(--green-50, #f0fdf4);
    border-color: var(--green-600, #079447);
    box-shadow: 0 4px 16px rgba(7, 148, 71, 0.12);
  }
  .workflow-step-btn.active .step-icon {
    background: var(--green-100);
    color: var(--green-800);
  }
  .workflow-step-btn.active strong {
    color: var(--green-950);
    font-weight: 800;
  }
  .workflow-step-btn.active span {
    color: var(--green-800);
  }
  @media (max-width: 640px) {
    .workflow-strip-filter {
      grid-template-columns: 1fr;
    }
  }
</style>

<!-- Filter Buttons: Catalog, Verified, Flagged -->
<div class="workflow-strip-filter">
  <a href="{{ route('admin.products.details', ['filter' => 'catalog']) }}"
     class="workflow-step-btn {{ ($filter ?? 'catalog') === 'catalog' ? 'active' : '' }}"
     data-filter="catalog"
     onclick="applyProductFilter(event, 'catalog')">
    <div class="step-icon">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8l-9 5-9-5 9-5 9 5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
    </div>
    <strong>Catalog</strong>
    <span>{{ $totalCount ?? $products->count() }}</span>
  </a>

  <a href="{{ route('admin.products.details', ['filter' => 'verified']) }}"
     class="workflow-step-btn {{ ($filter ?? 'catalog') === 'verified' ? 'active' : '' }}"
     data-filter="verified"
     onclick="applyProductFilter(event, 'verified')">
    <div class="step-icon">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
    </div>
    <strong>Verified</strong>
    <span>{{ $verifiedCount }}</span>
  </a>

  <a href="{{ route('admin.products.details', ['filter' => 'flagged']) }}"
     class="workflow-step-btn {{ ($filter ?? 'catalog') === 'flagged' ? 'active' : '' }}"
     data-filter="flagged"
     onclick="applyProductFilter(event, 'flagged')">
    <div class="step-icon">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    </div>
    <strong>Flagged</strong>
    <span>{{ $flaggedCount }}</span>
  </a>
</div>

<!-- Product Visual Grid -->
<div class="product-visual-grid">
  @foreach($products as $p)
    <article class="product-visual-card"
             data-verified="{{ $p->verified ? '1' : '0' }}"
             data-flags="{{ $p->flags_count }}"
             style="display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <div class="product-visual-hero" style="cursor:pointer;" onclick="previewProduct({{ $p->id }})" title="Click to view details">
          @if(!empty($p->image_url))
            <img src="{{ str_starts_with($p->image_url, 'http') ? $p->image_url : asset($p->image_url) }}" alt="{{ $p->name }}" loading="lazy" onerror="this.style.display='none';">
          @else
            <div class="product-glyph">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8l-9 5-9-5 9-5 9 5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
            </div>
          @endif
          <div class="corner-badge">
            @php $st = strtolower($p->status); @endphp
            <span class="badge {{ $st }}">{{ $p->status }}</span>
          </div>
        </div>
        <div class="product-visual-body" style="padding-bottom:10px;">
          <h4 style="cursor:pointer;" onclick="previewProduct({{ $p->id }})" title="Click to view details">{{ $p->name }}</h4>
          <p>{{ $p->brand }} · {{ $p->barcode }}</p>
          <div class="product-visual-meta">
            <span class="severity {{ $p->flags_count >= 3 ? 'red' : ($p->flags_count === 2 ? 'yellow' : 'green') }}">
              <i></i>
              {{ $p->flags_count }} flag{{ $p->flags_count === 1 ? '' : 's' }}
            </span>
            <div class="product-visual-actions">
              @if($p->verified)
                <span class="badge verified" style="height:22px;font-size:9px">Verified</span>
              @else
                <span class="badge pending" style="height:22px;font-size:9px">Pending</span>
              @endif
            </div>
          </div>
        </div>
      </div>
      <div style="padding:10px 13px 13px;display:flex;flex-wrap:wrap;gap:6px;border-top:1px solid var(--line);background:var(--surface);">
        <button type="button" class="btn btn-secondary btn-sm" style="flex:1;min-width:85px;justify-content:center;height:32px;font-size:11.5px;gap:4px;" onclick="previewProduct({{ $p->id }})">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          View Details
        </button>
        <button type="button" class="btn btn-secondary btn-sm" style="flex:1;min-width:75px;justify-content:center;height:32px;font-size:11.5px;gap:4px;" onclick="editProduct({{ $p->id }})">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </button>
        @if(!$p->verified)
          <form method="POST" action="{{ route('admin.products.verify', $p) }}" style="flex:1;min-width:115px;margin:0">
            @csrf
            <button type="submit" class="btn btn-sm" style="width:100%;justify-content:center;height:32px;font-size:11.5px;gap:4px;background:#059669;color:#fff;border-color:#059669" title="Verify product and publish immediately">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
              Verify &amp; Publish
            </button>
          </form>
        @endif
      </div>
    </article>
  @endforeach
</div>

@push('scripts')
@include('admin.products._product_scripts')
<script>
  function applyProductFilter(event, filterName) {
    if (event) {
      event.preventDefault();
    }

    // 1. Update active button classes
    document.querySelectorAll('.workflow-step-btn').forEach(btn => {
      if (btn.dataset.filter === filterName) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });

    // 2. Filter product cards
    const cards = document.querySelectorAll('.product-visual-card');
    let visibleCount = 0;

    cards.forEach(card => {
      const isVerified = card.dataset.verified === '1';
      const flags = parseInt(card.dataset.flags || '0', 10);

      let match = false;
      if (filterName === 'catalog') {
        match = true;
      } else if (filterName === 'verified') {
        match = isVerified;
      } else if (filterName === 'flagged') {
        match = (flags > 0);
      }

      if (match) {
        card.style.display = 'flex';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    // 3. Handle empty state
    let emptyEl = document.getElementById('filterEmptyState');
    if (!emptyEl) {
      emptyEl = document.createElement('div');
      emptyEl.id = 'filterEmptyState';
      emptyEl.style.cssText = 'grid-column: 1 / -1; padding: 48px 24px; text-align: center; color: var(--ink-500); background: var(--surface); border: 1px dashed var(--line); border-radius: 16px; margin: 12px 0;';
      emptyEl.innerHTML = '<strong style="display:block; font-size:14px; margin-bottom:4px; color:var(--ink-700);">No products found</strong><span>No products currently match this filter criteria.</span>';
      const grid = document.querySelector('.product-visual-grid');
      if (grid) grid.appendChild(emptyEl);
    }
    emptyEl.style.display = (visibleCount === 0) ? 'block' : 'none';

    // 4. Update browser URL query param
    const url = new URL(window.location.href);
    url.searchParams.set('filter', filterName);
    window.history.pushState({ filter: filterName }, '', url);
  }

  // Handle browser back/forward buttons
  window.addEventListener('popstate', (event) => {
    const params = new URLSearchParams(window.location.search);
    const filter = params.get('filter') || 'catalog';
    applyProductFilter(null, filter);
  });
</script>
@endpush
@endsection
