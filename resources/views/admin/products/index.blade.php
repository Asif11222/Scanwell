@extends('layouts.admin')

@section('title', 'ScanWell Admin - All Products')
@section('breadcrumb', 'All Products')
@section('page_title', 'All Products')

@section('content')
<div class="page-head">
  <div>
    <h2>All Products</h2>
    <p>Centralized product catalog records, retail barcodes, and verified package nutrition facts</p>
  </div>
  @if(auth('admin')->user()?->hasPermission('products.manage'))
  <div class="page-actions">
    <button type="button" class="btn btn-secondary" onclick="showToast('Bulk CSV product import is queued in worker.', 'info', 'CSV Import')">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 16V4M7 9l5-5 5 5"/><path d="M4 16v4h16v-4"/></svg>
      Import
    </button>
    <button type="button" class="btn btn-primary" onclick="openAddModal()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
      Add Product
    </button>
  </div>
  @endif
</div>

<div class="card">
  <!-- Toolbar Filters -->
  <form method="GET" action="{{ route('admin.products.index') }}" class="toolbar">
    <input type="search" name="q" value="{{ request('q') }}" class="filter-search" placeholder="Search name, brand or barcode…" />
    <select name="status" class="filter-select" onchange="this.form.submit()">
      <option value="All" {{ request('status') === 'All' ? 'selected' : '' }}>All Statuses</option>
      <option value="Published" {{ request('status') === 'Published' ? 'selected' : '' }}>Published</option>
      <option value="Draft" {{ request('status') === 'Draft' ? 'selected' : '' }}>Draft</option>
      <option value="Archived" {{ request('status') === 'Archived' ? 'selected' : '' }}>Archived</option>
    </select>
    <select name="category" class="filter-select" onchange="this.form.submit()">
      <option value="All" {{ request('category') === 'All' ? 'selected' : '' }}>All Categories</option>
      @foreach($categories as $cat)
        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
      @endforeach
    </select>
    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
    @if(request('q') || request('status') || request('category'))
      <a href="{{ route('admin.products.index') }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
    <div class="toolbar-spacer"></div>
    <span class="result-count">{{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }}</span>
  </form>

  <!-- Table -->
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Barcode</th>
          <th>Category</th>
          <th>Verification</th>
          <th>Health Flags</th>
          <th>Status</th>
          <th>Updated</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($products as $product)
          <tr>
            <td>
              <div class="product-cell">
                <div class="thumb">
                  @if(!empty($product->image_url))
                    <img src="{{ str_starts_with($product->image_url, 'http') ? $product->image_url : asset($product->image_url) }}" alt="{{ $product->name }}" loading="lazy" onerror="this.style.display='none'; this.parentElement.textContent='{{ strtoupper(substr($product->brand ?? $product->name, 0, 2)) }}';">
                  @else
                    {{ strtoupper(substr($product->brand, 0, 2)) }}
                  @endif
                </div>
                <div>
                  <div class="cell-main">{{ $product->name }}</div>
                  <div class="cell-sub">{{ $product->brand }} · {{ $product->country ?? 'Global' }}</div>
                </div>
              </div>
            </td>
            <td><code>{{ $product->barcode }}</code></td>
            <td>{{ $product->category }}</td>
            <td>
              @if($product->verified)
                <span class="badge verified">Verified</span>
              @else
                <span class="badge pending">Unverified</span>
              @endif
            </td>
            <td>
              <span class="severity {{ $product->flags_count >= 3 ? 'red' : ($product->flags_count === 2 ? 'yellow' : 'green') }}">
                <i></i>
                {{ $product->flags_count }} flag{{ $product->flags_count === 1 ? '' : 's' }}
              </span>
            </td>
            <td>
              @php $st = strtolower($product->status); @endphp
              <span class="badge {{ $st }}">{{ $product->status }}</span>
            </td>
            <td>{{ $product->updated_at->format('M d, Y') }}</td>
            <td>
              <div class="actions">
                <button type="button" class="mini-btn" title="View Details" onclick="previewProduct({{ $product->id }})">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
                @if(auth('admin')->user()?->hasPermission('products.manage') || auth('admin')->user()?->hasPermission('products.edit'))
                <button type="button" class="mini-btn" title="Edit Product" onclick="editProduct({{ $product->id }})">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/></svg>
                </button>
                @endif
                @if(auth('admin')->user()?->hasPermission('products.manage'))
                  @if(!$product->verified)
                    <form method="POST" action="{{ route('admin.products.verify', $product) }}" style="display:inline" onsubmit="return confirm('Verify {{ addslashes($product->name) }} and automatically publish to catalog?');">
                      @csrf
                      <button type="submit" class="mini-btn" title="Verify & Publish to Catalog" style="color:var(--green-700)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l4 4L19 6"/></svg>
                      </button>
                    </form>
                  @endif
                  <form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to delete \'{{ addslashes($product->name) }}\'? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="mini-btn delete-btn" title="Delete Product" style="color:var(--danger-strong)">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                    </button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="padding:36px 18px;text-align:center;color:var(--ink-500)">
              <div style="font-size:14px;font-weight:700">No products found</div>
              <p style="font-size:11px;margin:4px 0 0">Try clearing search keywords or changing filter criteria.</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="padding:12px 14px;border-top:1px solid var(--line-soft)">
    {{ $products->links() }}
  </div>
</div>

@push('scripts')
<script>
  function openAddModal() {
    openModal({
      title: 'Add New Product',
      subtitle: 'Custom validation will ensure barcode uniqueness and nutritional integrity.',
      size: 'wide',
      body: `
        <form id="createProductForm" method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
          @csrf
          <div class="form-grid">
            <div class="field full">
              <label>Product Picture</label>
              <div class="image-dropzone" id="addProductDropzone" onclick="document.getElementById('addProductImageInput').click()" ondragover="event.preventDefault(); this.classList.add('dragover');" ondragleave="this.classList.remove('dragover');" ondrop="event.preventDefault(); this.classList.remove('dragover'); handleProductImageDrop(event, 'addProductImageInput', 'addProductPreviewContainer', 'addProductDropzonePrompt');">
                <input type="file" name="image" id="addProductImageInput" accept="image/png,image/jpeg,image/webp,image/jpg,image/gif" style="display:none" onchange="handleProductImagePreview(this, 'addProductPreviewContainer', 'addProductDropzonePrompt')">
                <div id="addProductDropzonePrompt" style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:6px 0;">
                  <div style="width:38px;height:38px;border-radius:10px;background:var(--green-50);display:grid;place-items:center;color:var(--green-700)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                  </div>
                  <div style="font-size:12px;font-weight:650;color:var(--ink-800)">Upload Product Picture <span style="font-weight:normal;color:var(--ink-500)">(Click or drag image here)</span></div>
                  <div style="font-size:10px;color:var(--ink-400)">Supports PNG, JPG, WEBP or GIF up to 5MB. Appears in catalog & table.</div>
                </div>
                <div id="addProductPreviewContainer" style="display:none;width:100%"></div>
              </div>
            </div>
            <div class="field full">
              <label>Product Name *</label>
              <input name="name" class="input" placeholder="e.g. Oats & Honey Granola">
              <div class="product-name-error" style="display:none;color:#dc2626;font-size:11px;font-weight:600;margin-top:4px;"></div>
            </div>
            <div class="field">
              <label>Brand *</label>
              <input name="brand" class="input" required placeholder="e.g. Nature's Goodness">
            </div>
            <div class="field">
              <label>Barcode * (EAN/UPC)</label>
              <input name="barcode" class="input" required placeholder="e.g. 8901234567890">
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
              <label>Country of Origin</label>
              <select name="country" class="select">
                <option value="">Global / Unspecified</option>
                @foreach($countries as $c)
                  <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
              </select>
            </div>
            <div class="field">
              <label>Serving Size</label>
              <input name="serving_size" class="input" placeholder="e.g. 40 g">
            </div>
            <div class="field">
              <label>Manufacturer</label>
              <input name="manufacturer" class="input" placeholder="e.g. Healthy Life Foods Pvt. Ltd">
            </div>
            <div class="field full">
              <label>Ingredients Statement</label>
              <textarea name="ingredients" class="textarea" placeholder="Whole grain oats (54%), honey (15%), brown rice crisps..."></textarea>
            </div>
          </div>

          <div class="section-label" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:gap;gap:8px">
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
        <button type="button" class="btn btn-primary" onclick="submitProductForm('createProductForm', this)">Save & Evaluate Product</button>
      `
    });
  }

</script>
@include('admin.products._product_scripts')
@endpush
@endsection
