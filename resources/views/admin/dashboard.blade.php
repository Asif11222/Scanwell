@extends('layouts.admin')

@section('title', 'ScanWell Admin - Dashboard')
@section('breadcrumb', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="page-head">
  <div>
    <h2>Dashboard</h2>
    <p>ScanWell central control center · real-time scanner volume, OCR reviews, and catalog status</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.appControl.index') }}" class="btn btn-secondary" title="App runtime settings">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3"/><path d="M1 14h6M9 8h6M17 16h6"/></svg>
      App Control
    </a>
    <button type="button" class="btn btn-primary" onclick="openProductAddModal()">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
      Add Product
    </button>
  </div>
</div>

<!-- Visual KPI Grid -->
<div class="visual-kpi-grid">
  <div class="visual-kpi">
    <div class="visual-kpi-top">
      <div class="visual-kpi-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <span class="visual-kpi-trend">+8.2%</span>
    </div>
    <div class="visual-kpi-value">{{ number_format($usersCount) }}</div>
    <div class="visual-kpi-label">Total Users</div>
  </div>

  <div class="visual-kpi blue">
    <div class="visual-kpi-top">
      <div class="visual-kpi-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V5a1 1 0 0 1 1-1h2M17 4h2a1 1 0 0 1 1 1v2M20 17v2a1 1 0 0 1-1 1h-2M7 20H5a1 1 0 0 1-1-1v-2"/><path d="M7 12h10M8 9v6M11 9v6M14 9v6M17 9v6"/></svg>
      </div>
      <span class="visual-kpi-trend">{{ $scansToday > 0 ? '+12.4%' : '0%' }}</span>
    </div>
    <div class="visual-kpi-value">{{ number_format($scansToday) }}</div>
    <div class="visual-kpi-label">Scans Today</div>
  </div>

  <div class="visual-kpi purple">
    <div class="visual-kpi-top">
      <div class="visual-kpi-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8l-9 5-9-5 9-5 9 5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
      </div>
      <span class="visual-kpi-trend">+146 new</span>
    </div>
    <div class="visual-kpi-value">{{ number_format($productsCount) }}</div>
    <div class="visual-kpi-label">Catalog Products</div>
  </div>

  <div class="visual-kpi amber">
    <div class="visual-kpi-top">
      <div class="visual-kpi-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
      </div>
      <span class="visual-kpi-trend" style="background:var(--warning-bg);color:var(--warning-strong)">Needs Review</span>
    </div>
    <div class="visual-kpi-value">{{ $pendingReviews + $pendingCorrections + $unverifiedCount }}</div>
    <div class="visual-kpi-label">Pending Action</div>
  </div>
</div>

<!-- Graphical Overview -->
<div class="graphical-overview">
  <!-- Scan Activity Trend -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <h3>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M3 12h4l2-5 4 10 2-5h6"/></svg>
          Scan Activity (Last 7 Days)
        </h3>
      </div>
      <span class="badge active">Live Stream</span>
    </div>
    <div class="card-body">
      <div class="chart-wrap">
        @php
          $maxScan = max($scanTrend) > 0 ? max($scanTrend) : 1;
          $days = ['Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Today'];
        @endphp
        <div class="bar-chart">
          @foreach($scanTrend as $idx => $val)
            <div class="bar-col" title="{{ number_format($val) }} scans">
              <div class="bar" style="height: {{ $val > 0 ? max(10, round(($val / $maxScan) * 92)) : 0 }}%"></div>
              <div class="bar-label">{{ $days[$idx] }}</div>
            </div>
          @endforeach
        </div>
        <div class="chart-legend">
          <span><i class="legend-dot"></i>Product scans volume</span>
          <span>Peak volume: <strong>{{ number_format(max($scanTrend)) }} scans</strong></span>
        </div>
      </div>
    </div>
  </div>

  <!-- OCR Accuracy Ring -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <h3>OCR Intelligence</h3>
      </div>
    </div>
    <div class="card-body ring-card">
      <div class="ring-progress" style="--value:94.7;--ring:var(--green-700)">
        <strong>94.7%</strong>
        <span>Confidence</span>
      </div>
      <div class="ring-meta" style="flex:1">
        <h3>Extraction Health</h3>
        <div class="mini-metric">
          <span>Review Queue</span>
          <strong>{{ $pendingReviews }}</strong>
        </div>
        <div class="mini-metric">
          <span>Low Confidence</span>
          <strong>{{ $lowConfidenceCount }}</strong>
        </div>
        <div class="mini-metric">
          <span>Approved</span>
          <strong>{{ $approvedSubmissions }}</strong>
        </div>
      </div>
    </div>
  </div>

  <!-- Health Signals Distribution -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <h3>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M20.8 4.6a5.4 5.4 0 0 0-7.6 0L12 5.8l-1.2-1.2a5.4 5.4 0 0 0-7.6 7.6L12 21l8.8-8.8a5.4 5.4 0 0 0 0-7.6z"/></svg>
          Health Flag Signals
        </h3>
      </div>
    </div>
    <div class="card-body">
      <div class="health-signal-grid">
        <div class="health-signal red">
          <i></i>
          <strong>{{ $redSignals }}</strong>
          <span>Red Flag</span>
        </div>
        <div class="health-signal yellow">
          <i></i>
          <strong>{{ $yellowSignals }}</strong>
          <span>Caution</span>
        </div>
        <div class="health-signal green">
          <i></i>
          <strong>{{ $greenSignals }}</strong>
          <span>Looks Okay</span>
        </div>
      </div>
      <div class="mini-metric" style="margin-top:12px">
        <span>Published Rules</span>
        <strong>{{ $publishedRules }}</strong>
      </div>
      <div class="mini-metric">
        <span>Awaiting Review</span>
        <strong>{{ $rulesAwaitingReview }}</strong>
      </div>
    </div>
  </div>
</div>

<!-- Quick Action Navigation & Recent Audit Logs -->
<div class="grid grid-2" style="margin-top:16px">
  <!-- Quick Tools Grid -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <h3>Management Quick Tools</h3>
      </div>
    </div>
    <div class="card-body">
      <div class="icon-tile-grid">
        <a href="{{ route('admin.products.index') }}" class="icon-tile">
          <span class="tile-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8l-9 5-9-5 9-5 9 5z"/><path d="M3 8v8l9 5 9-5V8"/></svg>
          </span>
          <strong>All Products</strong>
          <span class="tile-count">{{ $productsCount }} records</span>
        </a>
        <a href="{{ route('admin.corrections.index') }}" class="icon-tile">
          <span class="tile-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/></svg>
          </span>
          <strong>Corrections</strong>
          <span class="tile-count">{{ $pendingCorrections }} pending</span>
        </a>
        <a href="{{ route('admin.submissions.index') }}" class="icon-tile">
          <span class="tile-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V5a1 1 0 0 1 1-1h2M17 4h2a1 1 0 0 1 1 1v2M20 17v2a1 1 0 0 1-1 1h-2M7 20H5a1 1 0 0 1-1-1v-2"/></svg>
          </span>
          <strong>OCR Review</strong>
          <span class="tile-count">{{ $pendingReviews }} waiting</span>
        </a>
        <a href="{{ route('admin.duplicates.index') }}" class="icon-tile">
          <span class="tile-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M15 9V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h4"/></svg>
          </span>
          <strong>Duplicates</strong>
          <span class="tile-count">{{ $duplicateCount }} matches</span>
        </a>
        <a href="{{ route('admin.products.verification') }}" class="icon-tile">
          <span class="tile-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </span>
          <strong>Verification</strong>
          <span class="tile-count">{{ $unverifiedCount }} unverified</span>
        </a>
        <a href="{{ route('admin.ads.index') }}" class="icon-tile">
          <span class="tile-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11v2a2 2 0 0 0 2 2h2l4 4V5L7 9H5a2 2 0 0 0-2 2zM11 8l8-3v14l-8-3M7 15v4"/></svg>
          </span>
          <strong>Campaigns</strong>
          <span class="tile-count">{{ $activeCampaigns }} active</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Recent Operational Activity -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <h3>Audit Activity Trail</h3>
      </div>
      <a href="{{ route('admin.security.index', ['tab' => 'audit']) }}" class="btn btn-secondary btn-sm">View Full Log</a>
    </div>
    <div class="card-body">
      <div style="display:flex;flex-direction:column">
        @forelse($activities as $act)
          <div style="display:flex;gap:12px;padding:11px 0;border-bottom:1px solid var(--line-soft)">
            <div style="width:9px;height:9px;border-radius:50%;background:var(--green-600);margin-top:5px;flex:none;box-shadow:0 0 0 3px var(--green-50)"></div>
            <div style="flex:1;min-width:0">
              <strong style="font-size:12px;color:var(--ink-900);display:block">{{ $act->action }}</strong>
              <p style="margin:2px 0 0;font-size:10.5px;color:var(--ink-500);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                {{ $act->detail }} · by {{ $act->user }}
              </p>
            </div>
            <span style="font-size:10px;color:var(--ink-400);white-space:nowrap">
              {{ $act->created_at->diffForHumans(null, true) }}
            </span>
          </div>
        @empty
          <div style="padding:20px;text-align:center;color:var(--ink-400)">No activity recorded yet.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function openProductAddModal() {
    openModal({
      title: 'Add New Product',
      subtitle: 'Catalog fields map directly to future mobile API and OCR intake',
      size: 'wide',
      body: `
        <form id="productForm" method="POST" action="{{ route('admin.products.store') }}">
          @csrf
          <div class="form-grid">
            <div class="field full">
              <label>Product Name *</label>
              <input name="name" class="input" placeholder="e.g. Lay's American Style Cream & Onion">
              <div class="product-name-error" style="display:none;color:#dc2626;font-size:11px;font-weight:600;margin-top:4px;"></div>
            </div>
            <div class="field">
              <label>Brand *</label>
              <input name="brand" class="input" required placeholder="e.g. Lay's">
            </div>
            <div class="field">
              <label>Barcode (EAN/UPC) *</label>
              <input name="barcode" class="input" required placeholder="e.g. 8901491101537">
            </div>
            <div class="field">
              <label>Category *</label>
              <select name="category" class="select" required>
                @foreach($categories as $cat)
                  <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
              </select>
            </div>
            <div class="field">
              <label>Status *</label>
              <select name="status" class="select" required>
                <option value="Draft" selected>Draft (Queued for Verification)</option>
                <option value="Archived">Archived</option>
              </select>
            </div>
            <div class="field">
              <label>Serving Size</label>
              <input name="serving_size" class="input" placeholder="e.g. 28 g">
            </div>
            <div class="field">
              <label>Manufacturer</label>
              <input name="manufacturer" class="input" placeholder="e.g. PepsiCo India">
            </div>
            <div class="field full">
              <label>Ingredients Statement</label>
              <textarea name="ingredients" class="textarea" placeholder="Enter ingredients declared on the package label..."></textarea>
            </div>
          </div>

          <div class="section-label" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <span>Nutritional Declaration (Evaluated by Health Intelligence Engine)</span>
            <div style="display:flex;align-items:center;gap:8px">
              <button type="button" class="btn-add-nutrient" onclick="addNutrientBox(this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                Add Nutrient
              </button>
              <span class="nutrition-counter-badge" style="font-size:11.5px;font-weight:600;padding:3px 10px;border-radius:20px;"></span>
            </div>
          </div>
          <div style="font-size:12px;color:var(--ink-500);margin:-6px 0 12px 0;">
            Any 3 nutrient boxes are required for health re-evaluation. Emptied or removed boxes will be cleared from product profile.
          </div>
          <div class="nutrient-error-placeholder"></div>
          <div class="nutrient-grid">
            <div class="field nutrient-box">
              <div class="nutrient-box-header">
                <input type="text" name="nutrient_names[]" class="nutrient-name-input" value="Sodium (mg)" placeholder="Nutrient Name (unit)" title="Click to edit nutrient name">
                <button type="button" class="nutrient-remove-btn" title="Remove nutrient box" onclick="removeNutrientBox(this)">&times;</button>
              </div>
              <div class="nutrient-name-error" style="color:#dc2626;font-size:10px;font-weight:600;margin:2px 0 5px;display:none;"></div>
              <input type="number" step="any" min="0" name="nutrient_values[]" class="input nutrient-value-input" placeholder="e.g. 210">
            </div>
            <div class="field nutrient-box">
              <div class="nutrient-box-header">
                <input type="text" name="nutrient_names[]" class="nutrient-name-input" value="Sugar (g)" placeholder="Nutrient Name (unit)" title="Click to edit nutrient name">
                <button type="button" class="nutrient-remove-btn" title="Remove nutrient box" onclick="removeNutrientBox(this)">&times;</button>
              </div>
              <div class="nutrient-name-error" style="color:#dc2626;font-size:10px;font-weight:600;margin:2px 0 5px;display:none;"></div>
              <input type="number" step="any" min="0" name="nutrient_values[]" class="input nutrient-value-input" placeholder="e.g. 12">
            </div>
            <div class="field nutrient-box">
              <div class="nutrient-box-header">
                <input type="text" name="nutrient_names[]" class="nutrient-name-input" value="Added Sugar (g)" placeholder="Nutrient Name (unit)" title="Click to edit nutrient name">
                <button type="button" class="nutrient-remove-btn" title="Remove nutrient box" onclick="removeNutrientBox(this)">&times;</button>
              </div>
              <div class="nutrient-name-error" style="color:#dc2626;font-size:10px;font-weight:600;margin:2px 0 5px;display:none;"></div>
              <input type="number" step="any" min="0" name="nutrient_values[]" class="input nutrient-value-input" placeholder="e.g. 5">
            </div>
            <div class="field nutrient-box">
              <div class="nutrient-box-header">
                <input type="text" name="nutrient_names[]" class="nutrient-name-input" value="Saturated Fat (g)" placeholder="Nutrient Name (unit)" title="Click to edit nutrient name">
                <button type="button" class="nutrient-remove-btn" title="Remove nutrient box" onclick="removeNutrientBox(this)">&times;</button>
              </div>
              <div class="nutrient-name-error" style="color:#dc2626;font-size:10px;font-weight:600;margin:2px 0 5px;display:none;"></div>
              <input type="number" step="any" min="0" name="nutrient_values[]" class="input nutrient-value-input" placeholder="e.g. 4.5">
            </div>
            <div class="field nutrient-box">
              <div class="nutrient-box-header">
                <input type="text" name="nutrient_names[]" class="nutrient-name-input" value="Trans Fat (g)" placeholder="Nutrient Name (unit)" title="Click to edit nutrient name">
                <button type="button" class="nutrient-remove-btn" title="Remove nutrient box" onclick="removeNutrientBox(this)">&times;</button>
              </div>
              <div class="nutrient-name-error" style="color:#dc2626;font-size:10px;font-weight:600;margin:2px 0 5px;display:none;"></div>
              <input type="number" step="any" min="0" name="nutrient_values[]" class="input nutrient-value-input" placeholder="e.g. 0">
            </div>
            <div class="field nutrient-box">
              <div class="nutrient-box-header">
                <input type="text" name="nutrient_names[]" class="nutrient-name-input" value="Calories (kcal)" placeholder="Nutrient Name (unit)" title="Click to edit nutrient name">
                <button type="button" class="nutrient-remove-btn" title="Remove nutrient box" onclick="removeNutrientBox(this)">&times;</button>
              </div>
              <div class="nutrient-name-error" style="color:#dc2626;font-size:10px;font-weight:600;margin:2px 0 5px;display:none;"></div>
              <input type="number" step="any" min="0" name="nutrient_values[]" class="input nutrient-value-input" placeholder="e.g. 150">
            </div>
            <div class="field nutrient-box">
              <div class="nutrient-box-header">
                <input type="text" name="nutrient_names[]" class="nutrient-name-input" value="Total Fat (g)" placeholder="Nutrient Name (unit)" title="Click to edit nutrient name">
                <button type="button" class="nutrient-remove-btn" title="Remove nutrient box" onclick="removeNutrientBox(this)">&times;</button>
              </div>
              <div class="nutrient-name-error" style="color:#dc2626;font-size:10px;font-weight:600;margin:2px 0 5px;display:none;"></div>
              <input type="number" step="any" min="0" name="nutrient_values[]" class="input nutrient-value-input" placeholder="e.g. 8">
            </div>
            <div class="field nutrient-box">
              <div class="nutrient-box-header">
                <input type="text" name="nutrient_names[]" class="nutrient-name-input" value="Protein (g)" placeholder="Nutrient Name (unit)" title="Click to edit nutrient name">
                <button type="button" class="nutrient-remove-btn" title="Remove nutrient box" onclick="removeNutrientBox(this)">&times;</button>
              </div>
              <div class="nutrient-name-error" style="color:#dc2626;font-size:10px;font-weight:600;margin:2px 0 5px;display:none;"></div>
              <input type="number" step="any" min="0" name="nutrient_values[]" class="input nutrient-value-input" placeholder="e.g. 3">
            </div>
          </div>

          <div class="section-label">Verification Queue Routing</div>
          <div class="notice" style="display:flex;align-items:center;gap:10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--warning-strong);flex-shrink:0"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            <span style="font-size:12px">All newly added products are saved as <strong>unverified Drafts</strong> and sent to the <strong>Verification Queue</strong>. Once verified, the status automatically switches to <strong>Published</strong>.</span>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="submitProductForm('productForm', this)">Save & Evaluate Product</button>
      `
    });
  }
</script>
@endpush
@endsection
