@extends('layouts.admin')

@section('title', 'ScanWell Admin - Master Data: ' . $tab)
@section('breadcrumb', 'Master Data / ' . $tab)
@section('page_title', 'Master Data — ' . $tab)

@section('content')
<div class="page-head">
  <div>
    <h2>Master Data: {{ $tab }}</h2>
    <p>Centralize standardized taxonomy values for ingredients, additives, allergens, and nutritional units</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn-primary" onclick="openAddMasterModal()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
      Add {{ \Illuminate\Support\Str::singular($tab) }}
    </button>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      <h3>{{ $tab }} Values</h3>
      <p>{{ $items->count() }} standardized master values available to product forms and rule builders</p>
    </div>
    <div class="card-actions">
      <button type="button" class="btn btn-primary btn-sm" onclick="openAddMasterModal()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        Add {{ \Illuminate\Support\Str::singular($tab) }}
      </button>
    </div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Standardized Name</th>
          <th>Taxonomy Type</th>
          <th>Status</th>
          <th>Updated</th>
          <th style="text-align:right;width:100px">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          <tr>
            <td>
              <div class="cell-main"><strong>{{ $item->name }}</strong></div>
            </td>
            <td><span class="badge scheduled">{{ $item->type }}</span></td>
            <td>
              <span class="badge {{ $item->is_active ? 'verified' : 'draft' }}">{{ $item->is_active ? 'Active' : 'Disabled' }}</span>
            </td>
            <td>{{ $item->updated_at ? $item->updated_at->format('M d, Y') : '—' }}</td>
            <td style="text-align:right">
              <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                <button type="button" class="mini-btn" title="Edit {{ \Illuminate\Support\Str::singular($tab) }}" onclick="openEditMasterModal({{ json_encode($item) }})">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/></svg>
                </button>
                <button type="button" class="mini-btn delete-btn" title="Delete {{ \Illuminate\Support\Str::singular($tab) }}" onclick="openDeleteMasterModal({{ $item->id }}, {{ json_encode($item->name) }}, {{ json_encode($item->type) }})">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="padding:36px;text-align:center;color:var(--ink-400)">
              No values configured under {{ $tab }}.
              <div style="margin-top:12px">
                <button type="button" class="btn btn-primary btn-sm" onclick="openAddMasterModal()">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                  Add First {{ \Illuminate\Support\Str::singular($tab) }}
                </button>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
  const taxonomyTypes = @json($types);

  function openAddMasterModal() {
    const optionsHtml = taxonomyTypes.map(t => 
      `<option value="${t}" ${t === '{{ $tab }}' ? 'selected' : ''}>${t}</option>`
    ).join('');

    openModal({
      title: 'Add Master Data Value',
      subtitle: 'Taxonomy Group: {{ $tab }}',
      body: `
        <form id="createMasterForm" method="POST" action="{{ route('admin.masterData.store') }}">
          @csrf
          <div class="form-grid">
            <div class="field full">
              <label>Taxonomy Group *</label>
              <select name="type" class="select" required>
                ${optionsHtml}
              </select>
            </div>
            <div class="field full">
              <label>Master Name / Standardized Identifier *</label>
              <input name="name" class="input" required placeholder="e.g. Organic Oat Milk, Gluten-Free..." autofocus>
            </div>
            <div class="field full">
              <div class="checkbox-row" style="margin-top:4px">
                <input type="checkbox" name="is_active" value="1" id="masterActive" checked>
                <label for="masterActive">Active and available in admin selectors and mobile filters</label>
              </div>
            </div>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('createMasterForm').submit()">Save Value</button>
      `
    });
  }

  function openEditMasterModal(item) {
    const optionsHtml = taxonomyTypes.map(t => 
      `<option value="${t}" ${t === item.type ? 'selected' : ''}>${t}</option>`
    ).join('');

    openModal({
      title: `Edit ${item.name}`,
      subtitle: `Taxonomy: ${item.type}`,
      body: `
        <form id="editMasterForm" method="POST" action="{{ url('admin/master-data') }}/${item.id}">
          @csrf
          @method('PUT')
          <div class="form-grid">
            <div class="field full">
              <label>Taxonomy Group *</label>
              <select name="type" class="select" required>
                ${optionsHtml}
              </select>
            </div>
            <div class="field full">
              <label>Standardized Name / Term *</label>
              <input name="name" class="input" value="${item.name || ''}" required autofocus>
            </div>
            <div class="field full">
              <div class="checkbox-row" style="margin-top:4px">
                <input type="checkbox" name="is_active" value="1" id="editMasterActive" ${item.is_active ? 'checked' : ''}>
                <label for="editMasterActive">Active and available across ScanWell systems</label>
              </div>
            </div>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('editMasterForm').submit()">Save Changes</button>
      `
    });
  }

  function openDeleteMasterModal(id, name, type) {
    openModal({
      title: `Delete ${name}`,
      subtitle: 'Taxonomy Governance Confirmation',
      body: `
        <form id="deleteMasterForm" method="POST" action="{{ url('admin/master-data') }}/${id}">
          @csrf
          @method('DELETE')
          <p style="margin:0;font-size:13.5px;line-height:1.5">
            Are you sure you want to permanently delete <strong>${name}</strong> from the <strong>${type}</strong> taxonomy?
          </p>
          <div class="info-strip warning" style="margin-top:14px;background:var(--danger-bg);border-color:var(--danger-border);color:var(--danger-strong)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3L2 20h20L12 3z"/><path d="M12 9v4M12 17h.01"/></svg>
            <div class="copy" style="font-size:12px">
              <strong>Impact Notice:</strong>
              <span>This term will be removed from product catalog selectors and ingredient/allergen auto-suggestions.</span>
            </div>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="document.getElementById('deleteMasterForm').submit()">Delete Permanently</button>
      `
    });
  }
</script>
@endpush
@endsection
