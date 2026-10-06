@extends('layouts.admin')

@section('title', 'ScanWell Admin - Health Concerns')
@section('breadcrumb', 'Health Concerns')
@section('page_title', 'Health Concerns')

@section('content')
<div class="page-head">
  <div>
    <h2>Health Concerns</h2>
    <p>Maintain the health profile taxonomy used to personalize ScanWell scan warnings and alternative suggestions</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn-primary" onclick="openAddConcernModal()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
      Add Concern
    </button>
  </div>
</div>

<div class="grid grid-3">
  @foreach($concerns as $c)
    <div class="card" style="padding:16px">
      <div style="display:flex;align-items:center;gap:12px">
        <div style="width:38px;height:38px;border-radius:11px;background:var(--green-50);color:var(--green-700);display:grid;place-items:center;flex:none">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.8 4.6a5.4 5.4 0 0 0-7.6 0L12 5.8l-1.2-1.2a5.4 5.4 0 0 0-7.6 7.6L12 21l8.8-8.8a5.4 5.4 0 0 0 0-7.6z"/></svg>
        </div>
        <div style="flex:1;min-width:0">
          <h4 style="margin:0;font-size:14px;color:var(--ink-950)">{{ $c->name }}</h4>
          <p style="margin:2px 0 0;font-size:10.5px;color:var(--ink-500);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            {{ $c->description ?? 'User health condition profile' }}
          </p>
        </div>
      </div>

      <div class="tag-row" style="margin:14px 0">
        @if(!empty($c->mapped_nutrients))
          @foreach($c->mapped_nutrients as $tag)
            <span class="tag">{{ $tag }}</span>
          @endforeach
        @endif
      </div>

      <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--line-soft);padding-top:10px;font-size:10.5px;color:var(--ink-500)">
        <span>{{ $c->rules_count }} mapped rules</span>
        <div style="display:flex;align-items:center;gap:8px">
          <span class="badge {{ $c->active ? 'verified' : 'paused' }}">{{ $c->active ? 'Active' : 'Paused' }}</span>
          <button type="button" class="mini-btn" onclick="openEditConcernModal({{ $c->id }})" title="Edit Concern">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/></svg>
          </button>
        </div>
      </div>
    </div>
  @endforeach
</div>

@push('scripts')
<script>
  function openAddConcernModal() {
    openModal({
      title: 'Add Health Concern Taxonomy',
      subtitle: 'Users will be able to select this condition in their mobile health profile',
      body: `
        <form id="createConcernForm" method="POST" action="{{ route('admin.health.concerns.store') }}">
          @csrf
          <div class="form-grid">
            <div class="field full">
              <label>Condition Name *</label>
              <input name="name" class="input" required placeholder="e.g. Celiac Disease">
            </div>
            <div class="field full">
              <label>Description & Dietary Scope</label>
              <textarea name="description" class="textarea" placeholder="Describe the health concern and what foods require caution..."></textarea>
            </div>
            <div class="field full">
              <label>Mapped Nutrients / Allergens (Comma separated)</label>
              <input name="mapped_nutrients" class="input" placeholder="e.g. Gluten, Wheat, Barley">
            </div>
            <div class="field">
              <label>Active Status</label>
              <select name="active" class="select">
                <option value="1" selected>Active</option>
                <option value="0">Paused</option>
              </select>
            </div>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('createConcernForm').submit()">Save Concern</button>
      `
    });
  }

  function openEditConcernModal(id) {
    fetch(`{{ url('admin/health-concerns') }}/${id}`)
      .then(res => res.json())
      .then(c => {
        const tags = Array.isArray(c.mapped_nutrients) ? c.mapped_nutrients.join(', ') : '';
        openModal({
          title: `Edit ${c.name}`,
          body: `
            <form id="editConcernForm" method="POST" action="{{ url('admin/health-concerns') }}/${c.id}">
              @csrf
              @method('PUT')
              <div class="form-grid">
                <div class="field full">
                  <label>Condition Name *</label>
                  <input name="name" class="input" value="${c.name || ''}" required>
                </div>
                <div class="field full">
                  <label>Description</label>
                  <textarea name="description" class="textarea">${c.description || ''}</textarea>
                </div>
                <div class="field full">
                  <label>Mapped Nutrients / Allergens</label>
                  <input name="mapped_nutrients" class="input" value="${tags}">
                </div>
                <div class="field">
                  <label>Status</label>
                  <select name="active" class="select">
                    <option value="1" ${c.active ? 'selected' : ''}>Active</option>
                    <option value="0" ${!c.active ? 'selected' : ''}>Paused</option>
                  </select>
                </div>
              </div>
            </form>
          `,
          footer: `
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('editConcernForm').submit()">Save Changes</button>
          `
        });
      });
  }
</script>
@endpush
@endsection
