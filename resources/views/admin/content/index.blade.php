@extends('layouts.admin')

@section('title', 'ScanWell Admin - App Content')
@section('breadcrumb', 'App Content')
@section('page_title', 'App Content')

@section('content')
<div class="page-head">
  <div>
    <h2>App Content</h2>
    <p>Manage in-app onboarding, authentication, scan review, and privacy strings without releasing a new Flutter update</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn-primary" onclick="openCreateContentModal()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
      Add Content Entry
    </button>
  </div>
</div>

<div class="card">
  <form method="GET" action="{{ route('admin.content.index') }}" class="toolbar">
    <select name="status" class="filter-select" onchange="this.form.submit()">
      <option value="All" {{ $status === 'All' ? 'selected' : '' }}>All Statuses</option>
      <option value="Published" {{ $status === 'Published' ? 'selected' : '' }}>Published</option>
      <option value="Review" {{ $status === 'Review' ? 'selected' : '' }}>Review</option>
      <option value="Draft" {{ $status === 'Draft' ? 'selected' : '' }}>Draft</option>
    </select>
    <div class="toolbar-spacer"></div>
    <span class="result-count">{{ $entries->count() }} content string{{ $entries->count() === 1 ? '' : 's' }}</span>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Content Key</th>
          <th>Area</th>
          <th>Locale</th>
          <th>Text Preview</th>
          <th>Status</th>
          <th>Editor</th>
          <th style="text-align:right">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($entries as $entry)
          <tr>
            <td>
              <div class="cell-main"><code>{{ $entry->content_key }}</code></div>
              <div class="cell-sub">{{ $entry->title }}</div>
            </td>
            <td><strong>{{ $entry->area }}</strong></td>
            <td><span class="badge scheduled">{{ $entry->locale }}</span></td>
            <td>
              <div class="cell-sub" style="max-width:320px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                {{ $entry->body }}
              </div>
            </td>
            <td>
              @php $st = strtolower($entry->status); @endphp
              <span class="badge {{ $st }}">{{ $entry->status }}</span>
            </td>
            <td>{{ $entry->editor ?? 'System' }}</td>
            <td>
              <div class="actions">
                <button type="button" class="mini-btn" title="Edit Content String" onclick="openEditContentModal({{ $entry->id }})">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/></svg>
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" style="padding:32px;text-align:center;color:var(--ink-400)">No content strings configured.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
  function openCreateContentModal() {
    openModal({
      title: 'Add Dynamic Content Entry',
      subtitle: 'Dot-notation key convention e.g. onboarding.health_flags.title',
      size: 'wide',
      body: `
        <form id="createContentForm" method="POST" action="{{ route('admin.content.store') }}">
          @csrf
          <div class="form-grid">
            <div class="field">
              <label>Content Key * (dot-notation)</label>
              <input name="content_key" class="input" required placeholder="e.g. scan.results.helper">
            </div>
            <div class="field">
              <label>App Section / Area *</label>
              <select name="area" class="select" required>
                <option value="Onboarding">Onboarding</option>
                <option value="Authentication">Authentication</option>
                <option value="Scan workflow" selected>Scan Workflow</option>
                <option value="Privacy">Privacy & Disclosures</option>
                <option value="Empty state">Empty States</option>
                <option value="Help">Help & Support</option>
              </select>
            </div>
            <div class="field">
              <label>Locale *</label>
              <input name="locale" class="input" value="en-US" required>
            </div>
            <div class="field">
              <label>Status *</label>
              <select name="status" class="select" required>
                <option value="Published" selected>Published</option>
                <option value="Review">Review</option>
                <option value="Draft">Draft</option>
              </select>
            </div>
            <div class="field full">
              <label>Title / Header *</label>
              <input name="title" class="input" required placeholder="e.g. Scanning Your Packaged Food">
            </div>
            <div class="field full">
              <label>Body Text *</label>
              <textarea name="body" class="textarea" required placeholder="Enter mobile display copy..."></textarea>
            </div>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('createContentForm').submit()">Save Content</button>
      `
    });
  }

  function openEditContentModal(id) {
    fetch(`{{ url('admin/content') }}/${id}`)
      .then(res => res.json())
      .then(c => {
        openModal({
          title: `Edit Content: ${c.content_key}`,
          size: 'wide',
          body: `
            <form id="editContentForm" method="POST" action="{{ url('admin/content') }}/${c.id}">
              @csrf
              @method('PUT')
              <div class="form-grid">
                <div class="field">
                  <label>Content Key *</label>
                  <input name="content_key" class="input" value="${c.content_key || ''}" required>
                </div>
                <div class="field">
                  <label>Section Area *</label>
                  <input name="area" class="input" value="${c.area || ''}" required>
                </div>
                <div class="field">
                  <label>Locale *</label>
                  <input name="locale" class="input" value="${c.locale || 'en-US'}" required>
                </div>
                <div class="field">
                  <label>Status *</label>
                  <select name="status" class="select" required>
                    <option value="Published" ${c.status === 'Published' ? 'selected' : ''}>Published</option>
                    <option value="Review" ${c.status === 'Review' ? 'selected' : ''}>Review</option>
                    <option value="Draft" ${c.status === 'Draft' ? 'selected' : ''}>Draft</option>
                  </select>
                </div>
                <div class="field full">
                  <label>Title *</label>
                  <input name="title" class="input" value="${c.title || ''}" required>
                </div>
                <div class="field full">
                  <label>Body Copy *</label>
                  <textarea name="body" class="textarea" required>${c.body || ''}</textarea>
                </div>
              </div>
            </form>
          `,
          footer: `
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('editContentForm').submit()">Save Changes</button>
          `
        });
      });
  }
</script>
@endpush
@endsection
