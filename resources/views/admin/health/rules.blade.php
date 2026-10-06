@extends('layouts.admin')

@section('title', 'ScanWell Admin - Health Intelligence')
@section('breadcrumb', 'Health Intelligence')
@section('page_title', 'Health Intelligence')

@section('content')
<div class="page-head">
  <div>
    <h2>Health Intelligence</h2>
    <p>Configure versioned clinical and dietary rules evaluating nutrients and additives against health profiles</p>
  </div>
  @if(auth('admin')->user()?->hasPermission('health.manage'))
  <div class="page-actions">
    <button type="button" class="btn btn-primary" onclick="openAddRuleModal()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
      Add Health Rule
    </button>
  </div>
  @endif
</div>

<div class="info-strip warning">
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
  <div class="copy">
    <strong>Safety Governance Workflow: Draft → Review → Published → Archived</strong>
    <span>Health-impacting nutritional rules require clinical or content review before entering live mobile evaluation.</span>
  </div>
</div>

<div class="health-safety">
  <div class="safety-card red">
    <strong>Red / High Concern</strong>
    <span>Strong caution or avoid-style guidance (e.g. sodium exceeding 200mg for hypertension).</span>
  </div>
  <div class="safety-card yellow">
    <strong>Yellow / Use With Caution</strong>
    <span>Contextual caution encouraging label moderation (e.g. added sugar in diabetes profile).</span>
  </div>
  <div class="safety-card green">
    <strong>Green / Looks Okay</strong>
    <span>Lower concern signal without implying absolute medical treatment safety.</span>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Rule</th>
          <th>Target</th>
          <th>Threshold</th>
          <th>Severity</th>
          <th>Health Concern</th>
          <th>Version</th>
          <th>Status</th>
          <th>Effective</th>
          <th style="text-align:right">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rules as $r)
          <tr>
            <td>
              <div class="cell-main">{{ $r->name }}</div>
              <div class="cell-sub">Priority {{ $r->priority }} · {{ $r->source ?? 'Internal guideline' }}</div>
            </td>
            <td><strong>{{ $r->target }}</strong></td>
            <td><code>{{ $r->operator }} {{ $r->threshold }} {{ $r->unit }}</code></td>
            <td>
              @php
                $sevClass = str_starts_with(strtolower($r->severity), 'red') ? 'red' : (str_starts_with(strtolower($r->severity), 'yellow') ? 'yellow' : 'green');
              @endphp
              <span class="severity {{ $sevClass }}">
                <i></i>
                {{ $r->severity }}
              </span>
            </td>
            <td>{{ $r->concern ?? 'General' }}</td>
            <td><span class="badge scheduled">v{{ $r->version }}</span></td>
            <td>
              @php $st = strtolower($r->status); @endphp
              <span class="badge {{ $st }}">{{ $r->status }}</span>
            </td>
            <td><span class="cell-sub">{{ $r->effective_date ?? 'Immediate' }}</span></td>
            <td>
              <div class="actions">
                @if(auth('admin')->user()?->hasPermission('health.manage'))
                <button type="button" class="mini-btn" title="Edit Rule" onclick="openEditRuleModal({{ $r->id }})">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/></svg>
                </button>
                <button type="button" class="mini-btn delete-btn" title="Delete Rule" onclick="openDeleteRuleModal({{ $r->id }}, {{ json_encode($r->name) }}, {{ json_encode($r->target) }})">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                </button>
                @else
                <span style="font-size:11px;color:var(--ink-400);font-style:italic">Read-only</span>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" style="padding:32px;text-align:center;color:var(--ink-400)">No health evaluation rules configured yet.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
  async function submitRuleForm(formId, submitBtn) {
    const form = document.getElementById(formId);
    if (!form) return;

    // Clear previous errors & highlights
    form.querySelectorAll('.modal-form-errors').forEach(el => el.remove());
    form.querySelectorAll('.input, .select, .textarea').forEach(el => {
      el.style.borderColor = '';
    });

    const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = `
        <svg style="display:inline-block;vertical-align:middle;margin-right:6px;animation:spin 1s linear infinite" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <circle cx="12" cy="12" r="10" stroke-opacity="0.25" stroke="currentColor"/>
          <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor"/>
        </svg>
        Saving...
      `;
    }

    const formData = new FormData(form);

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        // Validation error (422) or server feedback
        let errorList = [];
        if (data.errors && typeof data.errors === 'object') {
          Object.entries(data.errors).forEach(([field, msgs]) => {
            if (Array.isArray(msgs)) {
              errorList.push(...msgs);
            } else if (typeof msgs === 'string') {
              errorList.push(msgs);
            }
            const fieldEl = form.querySelector(`[name="${field}"]`);
            if (fieldEl) {
              fieldEl.style.borderColor = 'var(--danger, #dc2626)';
              fieldEl.addEventListener('input', () => {
                fieldEl.style.borderColor = '';
              }, { once: true });
            }
          });
        } else if (data.message) {
          errorList.push(data.message);
        } else {
          errorList.push('An unexpected error occurred. Please check the rule fields and try again.');
        }

        const errorHtml = `
          <div class="info-strip warning modal-form-errors" style="margin-bottom:18px;background:var(--danger-bg);border:1px solid var(--danger-border);color:var(--danger-strong)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px"><path d="M12 3L2 20h20L12 3z"/><path d="M12 9v4M12 17h.01"/></svg>
            <div class="copy" style="color:var(--danger-strong)">
              <strong style="font-size:11.8px;display:block;color:var(--danger-strong)">Validation Feedback</strong>
              <ul style="margin:4px 0 0;padding-left:16px;color:var(--danger-strong);line-height:1.5">
                ${errorList.map(msg => `<li>${msg}</li>`).join('')}
              </ul>
            </div>
          </div>
        `;

        const targetSlot = form.querySelector('.modal-form-errors-container');
        if (targetSlot) {
          targetSlot.innerHTML = errorHtml;
        } else {
          form.insertAdjacentHTML('afterbegin', errorHtml);
        }

        const modalBody = form.closest('.modal-body');
        if (modalBody) {
          modalBody.scrollTop = 0;
        }

        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnHtml;
        }
        return;
      }

      // Success
      closeModal();
      if (typeof showToast === 'function') {
        showToast(data.message || 'Health rule saved successfully.', 'success');
      }
      setTimeout(() => {
        window.location.reload();
      }, 450);

    } catch (err) {
      console.error(err);
      const errorHtml = `
        <div class="info-strip warning modal-form-errors" style="margin-bottom:18px;background:var(--danger-bg);border:1px solid var(--danger-border);color:var(--danger-strong)">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px"><path d="M12 3L2 20h20L12 3z"/><path d="M12 9v4M12 17h.01"/></svg>
          <div class="copy" style="color:var(--danger-strong)">
            <strong style="font-size:11.8px;display:block;color:var(--danger-strong)">Validation Feedback</strong>
            <ul style="margin:4px 0 0;padding-left:16px;color:var(--danger-strong);line-height:1.5">
              <li>Server connection error. Please try again.</li>
            </ul>
          </div>
        </div>
      `;
      const targetSlot = form.querySelector('.modal-form-errors-container');
      if (targetSlot) {
        targetSlot.innerHTML = errorHtml;
      } else {
        form.insertAdjacentHTML('afterbegin', errorHtml);
      }
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHtml;
      }
    }
  }

  function openAddRuleModal() {
    openModal({
      title: 'Create Health Intelligence Rule',
      subtitle: 'Custom validation ensures operator-threshold consistency and medical policy compliance',
      size: 'wide',
      body: `
        <form id="createRuleForm" method="POST" action="{{ route('admin.health.rules.store') }}" novalidate onsubmit="event.preventDefault(); submitRuleForm('createRuleForm', document.getElementById('btnSubmitAddRule'))">
          @csrf
          <div class="modal-form-errors-container"></div>
          <div class="form-grid">
            <div class="field full">
              <label>Rule Name *</label>
              <input name="name" class="input" required placeholder="e.g. High sodium — general packaged food">
            </div>
            <div class="field">
              <label>Target Nutrient / Additive *</label>
              <select name="target" class="select" required>
                <optgroup label="Nutrients">
                  @foreach($nutrients as $n)
                    <option value="{{ $n }}">{{ $n }}</option>
                  @endforeach
                </optgroup>
                <optgroup label="Additives & Chemicals">
                  @foreach($additives as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                  @endforeach
                </optgroup>
              </select>
            </div>
            <div class="field">
              <label>Operator *</label>
              <select name="operator" class="select" required>
                <option value=">=" selected>>= (Greater than or equal)</option>
                <option value=">">> (Greater than)</option>
                <option value="<="><= (Less than or equal)</option>
                <option value="<">< (Less than)</option>
                <option value="=">= (Equals)</option>
                <option value="contains">contains (Ingredient presence)</option>
              </select>
            </div>
            <div class="field">
              <label>Threshold Value</label>
              <input name="threshold" class="input" placeholder="e.g. 200">
            </div>
            <div class="field">
              <label>Measurement Unit</label>
              <select name="unit" class="select">
                <option value="">None / Text</option>
                @foreach($units as $u)
                  <option value="{{ $u }}">{{ $u }}</option>
                @endforeach
              </select>
            </div>
            <div class="field">
              <label>Severity Category *</label>
              <select name="severity" class="select" required>
                <option value="Red / High Concern">Red / High Concern</option>
                <option value="Yellow / Use With Caution" selected>Yellow / Use With Caution</option>
                <option value="Green / Looks Okay">Green / Looks Okay</option>
              </select>
            </div>
            <div class="field">
              <label>Health Concern Taxonomy</label>
              <select name="health_concern_id" class="select">
                <option value="">General (All Profiles)</option>
                @foreach($concerns as $c)
                  <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="field">
              <label>Governance Status *</label>
              <select name="status" class="select" required>
                <option value="Draft" selected>Draft</option>
                <option value="Review">Review (Clinical Check)</option>
                <option value="Published">Published (Live in App)</option>
                <option value="Archived">Archived</option>
              </select>
            </div>
            <div class="field">
              <label>Evaluation Priority (1 - 100) *</label>
              <input name="priority" type="number" min="1" max="100" class="input" value="75" required>
            </div>
            <div class="field full">
              <label>Consumer Advisory Message *</label>
              <textarea name="message" class="textarea" required placeholder="Alert text displayed when this rule triggers on a product..."></textarea>
            </div>
            <div class="field full">
              <label>Dietary Recommendation</label>
              <textarea name="recommendation" class="textarea" placeholder="Actionable guidance (e.g. Choose lower-sodium alternative)..."></textarea>
            </div>
            <div class="field full">
              <label>Medical Reference / Guideline Citation</label>
              <input name="source" class="input" placeholder="e.g. WHO Guidelines on Sodium Intake">
            </div>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" id="btnSubmitAddRule" class="btn btn-primary" onclick="submitRuleForm('createRuleForm', this)">Save Rule</button>
      `
    });
  }

  function openEditRuleModal(id) {
    fetch(`{{ url('admin/health-intelligence') }}/${id}`)
      .then(res => res.json())
      .then(r => {
        openModal({
          title: `Edit Health Rule v${r.version}`,
          subtitle: `${r.name}`,
          size: 'wide',
          body: `
            <form id="editRuleForm" method="POST" action="{{ url('admin/health-intelligence') }}/${r.id}" novalidate onsubmit="event.preventDefault(); submitRuleForm('editRuleForm', document.getElementById('btnSubmitEditRule'))">
              @csrf
              @method('PUT')
              <div class="modal-form-errors-container"></div>
              <div class="form-grid">
                <div class="field full">
                  <label>Rule Name *</label>
                  <input name="name" class="input" value="${r.name || ''}" required>
                </div>
                <div class="field">
                  <label>Target *</label>
                  <input name="target" class="input" value="${r.target || ''}" required>
                </div>
                <div class="field">
                  <label>Operator *</label>
                  <select name="operator" class="select" required>
                    <option value=">=" ${r.operator === '>=' ? 'selected' : ''}>>=</option>
                    <option value=">" ${r.operator === '>' ? 'selected' : ''}>></option>
                    <option value="<=" ${r.operator === '<=' ? 'selected' : ''}><=</option>
                    <option value="<" ${r.operator === '<' ? 'selected' : ''}><</option>
                    <option value="=" ${r.operator === '=' ? 'selected' : ''}>=</option>
                    <option value="contains" ${r.operator === 'contains' ? 'selected' : ''}>contains</option>
                  </select>
                </div>
                <div class="field">
                  <label>Threshold</label>
                  <input name="threshold" class="input" value="${r.threshold || ''}">
                </div>
                <div class="field">
                  <label>Unit</label>
                  <input name="unit" class="input" value="${r.unit || ''}">
                </div>
                <div class="field">
                  <label>Severity *</label>
                  <select name="severity" class="select" required>
                    <option value="Red / High Concern" ${r.severity === 'Red / High Concern' ? 'selected' : ''}>Red / High Concern</option>
                    <option value="Yellow / Use With Caution" ${r.severity === 'Yellow / Use With Caution' ? 'selected' : ''}>Yellow / Use With Caution</option>
                    <option value="Green / Looks Okay" ${r.severity === 'Green / Looks Okay' ? 'selected' : ''}>Green / Looks Okay</option>
                  </select>
                </div>
                <div class="field">
                  <label>Status *</label>
                  <select name="status" class="select" required>
                    <option value="Draft" ${r.status === 'Draft' ? 'selected' : ''}>Draft</option>
                    <option value="Review" ${r.status === 'Review' ? 'selected' : ''}>Review</option>
                    <option value="Published" ${r.status === 'Published' ? 'selected' : ''}>Published</option>
                    <option value="Archived" ${r.status === 'Archived' ? 'selected' : ''}>Archived</option>
                  </select>
                </div>
                <div class="field">
                  <label>Priority *</label>
                  <input name="priority" type="number" class="input" value="${r.priority || 50}" required>
                </div>
                <div class="field full">
                  <label>Alert Message *</label>
                  <textarea name="message" class="textarea" required>${r.message || ''}</textarea>
                </div>
                <div class="field full">
                  <label>Recommendation</label>
                  <textarea name="recommendation" class="textarea">${r.recommendation || ''}</textarea>
                </div>
              </div>
            </form>
          `,
          footer: `
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button type="button" id="btnSubmitEditRule" class="btn btn-primary" onclick="submitRuleForm('editRuleForm', this)">Update Rule</button>
          `
        });
      });
  }

  function openDeleteRuleModal(id, name, target) {
    openModal({
      title: `Delete Health Rule`,
      subtitle: `Target: ${target}`,
      body: `
        <form id="deleteRuleForm" method="POST" action="{{ url('admin/health-intelligence') }}/${id}">
          @csrf
          @method('DELETE')
          <p style="margin:0;font-size:13.5px;line-height:1.5">
            Are you sure you want to permanently delete rule <strong>${name}</strong>?
          </p>
          <div class="info-strip warning" style="margin-top:14px;background:var(--danger-bg);border-color:var(--danger-border);color:var(--danger-strong)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3L2 20h20L12 3z"/><path d="M12 9v4M12 17h.01"/></svg>
            <div class="copy" style="font-size:12px">
              <strong>Clinical & Evaluation Impact:</strong>
              <span>Scanned products evaluating ${target} will no longer trigger this alert message or severity flag.</span>
            </div>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="document.getElementById('deleteRuleForm').submit()">Delete Rule</button>
      `
    });
  }

  @if($errors->any() && ($errors->has('name') || $errors->has('target') || $errors->has('operator') || $errors->has('threshold') || $errors->has('unit') || $errors->has('severity') || $errors->has('priority') || $errors->has('message')))
  document.addEventListener('DOMContentLoaded', function() {
    const pageAlert = document.getElementById('pageValidationAlert');
    if (pageAlert) {
      pageAlert.style.display = 'none';
    }

    openAddRuleModal();

    const form = document.getElementById('createRuleForm');
    if (form) {
      @if(old('name'))
        const nameInput = form.querySelector('[name="name"]');
        if (nameInput) nameInput.value = @json(old('name'));
      @endif
      @if(old('target'))
        const targetSelect = form.querySelector('[name="target"]');
        if (targetSelect) targetSelect.value = @json(old('target'));
      @endif
      @if(old('operator'))
        const opSelect = form.querySelector('[name="operator"]');
        if (opSelect) opSelect.value = @json(old('operator'));
      @endif
      @if(old('threshold'))
        const thInput = form.querySelector('[name="threshold"]');
        if (thInput) thInput.value = @json(old('threshold'));
      @endif
      @if(old('unit'))
        const unitSelect = form.querySelector('[name="unit"]');
        if (unitSelect) unitSelect.value = @json(old('unit'));
      @endif
      @if(old('severity'))
        const sevSelect = form.querySelector('[name="severity"]');
        if (sevSelect) sevSelect.value = @json(old('severity'));
      @endif
      @if(old('health_concern_id'))
        const hcSelect = form.querySelector('[name="health_concern_id"]');
        if (hcSelect) hcSelect.value = @json(old('health_concern_id'));
      @endif
      @if(old('status'))
        const stSelect = form.querySelector('[name="status"]');
        if (stSelect) stSelect.value = @json(old('status'));
      @endif
      @if(old('priority'))
        const priInput = form.querySelector('[name="priority"]');
        if (priInput) priInput.value = @json(old('priority'));
      @endif
      @if(old('message'))
        const msgTextarea = form.querySelector('[name="message"]');
        if (msgTextarea) msgTextarea.value = @json(old('message'));
      @endif
      @if(old('recommendation'))
        const recTextarea = form.querySelector('[name="recommendation"]');
        if (recTextarea) recTextarea.value = @json(old('recommendation'));
      @endif
      @if(old('source'))
        const srcInput = form.querySelector('[name="source"]');
        if (srcInput) srcInput.value = @json(old('source'));
      @endif

      const errorList = @json($errors->all());
      const errorHtml = `
        <div class="info-strip warning modal-form-errors" style="margin-bottom:18px;background:var(--danger-bg);border:1px solid var(--danger-border);color:var(--danger-strong)">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px"><path d="M12 3L2 20h20L12 3z"/><path d="M12 9v4M12 17h.01"/></svg>
          <div class="copy" style="color:var(--danger-strong)">
            <strong style="font-size:11.8px;display:block;color:var(--danger-strong)">Validation Feedback</strong>
            <ul style="margin:4px 0 0;padding-left:16px;color:var(--danger-strong);line-height:1.5">
              ${errorList.map(msg => `<li>${msg}</li>`).join('')}
            </ul>
          </div>
        </div>
      `;
      const targetSlot = form.querySelector('.modal-form-errors-container');
      if (targetSlot) {
        targetSlot.innerHTML = errorHtml;
      } else {
        form.insertAdjacentHTML('afterbegin', errorHtml);
      }
    }
  });
  @endif
</script>
@endpush
@endsection

