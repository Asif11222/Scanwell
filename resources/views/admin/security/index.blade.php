@extends('layouts.admin')

@section('title', 'ScanWell Admin - Security & Access')
@section('breadcrumb', 'Admin & Security')
@section('page_title', 'Admin & Security')

@section('content')
<style>
  .matrix-check-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    position: relative;
    user-select: none;
    margin: 0;
    padding: 3px;
  }
  .matrix-check-wrap input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
  }
  .matrix-check-box {
    width: 18px;
    height: 18px;
    border-radius: 4px;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .15s ease-in-out;
    box-sizing: border-box;
  }
  .matrix-check-wrap:hover .matrix-check-box {
    border-color: #079447;
    box-shadow: 0 0 0 2px rgba(7, 148, 71, 0.12);
  }
  .matrix-check-wrap input[type="checkbox"]:checked + .matrix-check-box {
    background: #079447;
    border-color: #079447;
  }
  .matrix-check-wrap input[type="checkbox"]:checked + .matrix-check-box svg {
    opacity: 1;
    transform: scale(1);
  }
  .matrix-check-box svg {
    opacity: 0;
    transform: scale(0.6);
    transition: all .12s ease-in-out;
  }
  .matrix-check-wrap.disabled {
    cursor: not-allowed;
  }
  .matrix-check-wrap.disabled .matrix-check-box {
    background: #059669 !important;
    border-color: #059669 !important;
    opacity: 0.92;
    box-shadow: none !important;
  }
  .matrix-check-wrap.disabled input[type="checkbox"]:checked + .matrix-check-box svg {
    opacity: 1;
    transform: scale(1);
  }
  @keyframes alertPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.015); }
    100% { transform: scale(1); }
  }
  .declined-pulse {
    animation: alertPulse 0.3s ease-in-out;
  }
</style>

<div class="page-head">
  <div>
    <h2>Admin & Security</h2>
    <p>Manage administrative access, separated permissions for clinical health rules, and audit logs</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn-primary" onclick="openAddAdminModal()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
      Add Admin / Manager
    </button>
  </div>
</div>

<div class="card">
  <div class="tabs">
    <a href="{{ route('admin.security.index', ['tab' => 'admins']) }}" class="tab {{ $tab === 'admins' ? 'active' : '' }}">Admin Users</a>
    <a href="{{ route('admin.security.index', ['tab' => 'roles']) }}" class="tab {{ $tab === 'roles' ? 'active' : '' }}">Roles & Policies</a>
    <a href="{{ route('admin.security.index', ['tab' => 'matrix']) }}" class="tab {{ $tab === 'matrix' ? 'active' : '' }}">Permission Matrix</a>
    <a href="{{ route('admin.security.index', ['tab' => 'audit']) }}" class="tab {{ $tab === 'audit' ? 'active' : '' }}">Audit Log</a>
    <a href="{{ route('admin.security.index', ['tab' => 'logins']) }}" class="tab {{ $tab === 'logins' ? 'active' : '' }}">Security Settings</a>
  </div>

  @if($tab === 'admins')
    <div class="toolbar" style="padding:14px 18px;border-bottom:1px solid var(--line-soft);display:flex;align-items:center;justify-content:space-between">
      <div>
        <strong style="font-size:13.5px">Administrative Team</strong>
        <span style="font-size:11px;color:var(--ink-500);margin-left:8px">{{ $admins->count() }} staff members & managers configured</span>
      </div>
      <button type="button" class="btn btn-primary btn-sm" onclick="openAddAdminModal()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        Add Staff / Manager
      </button>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Admin Staff</th>
            <th>Role</th>
            <th>Status</th>
            <th>Last Active</th>
            <th style="text-align:right;width:100px">Actions</th>
          </tr>
        </thead>
        <tbody>
          @php $currentAuthId = auth('admin')->id(); @endphp
          @foreach($admins as $a)
            @php $isSelf = ($a->id === $currentAuthId); @endphp
            <tr>
              <td>
                <div class="product-cell">
                  <div class="avatar">{{ strtoupper(substr($a->name, 0, 2)) }}</div>
                  <div>
                    <div class="cell-main">
                      {{ $a->name }}
                      @if($isSelf)
                        <span class="badge scheduled" style="font-size:9.5px;padding:2px 6px;margin-left:6px;vertical-align:middle">You</span>
                      @endif
                    </div>
                    <div class="cell-sub">{{ $a->email }}</div>
                  </div>
                </div>
              </td>
              <td><strong>{{ $a->role }}</strong></td>
              <td>
                <span class="badge {{ $a->status === 'Active' ? 'verified' : 'paused' }}">{{ $a->status }}</span>
              </td>
              <td>{{ $a->last_login_at ? $a->last_login_at->diffForHumans() : 'Active session' }}</td>
              <td style="text-align:right">
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                  <button type="button" class="mini-btn" title="Edit Staff Member" onclick="openEditAdminModal({{ json_encode($a) }})">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/></svg>
                  </button>
                  @if($isSelf)
                    <button type="button" class="mini-btn" disabled title="You cannot remove your own active account" style="opacity:0.35;cursor:not-allowed">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                    </button>
                  @else
                    <button type="button" class="mini-btn delete-btn" title="Remove Staff Member" onclick="openDeleteAdminModal({{ $a->id }}, {{ json_encode($a->name) }}, {{ json_encode($a->role) }})">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @elseif($tab === 'roles')
    <div class="card-body">
      <div class="grid grid-3">
        @foreach($roles as $r)
          <div class="card" style="padding:14px;border-color:var(--line)">
            <h4 style="margin:0;font-size:13px;color:var(--ink-900)">{{ $r[0] }}</h4>
            @if(!empty($r[1]))
              <p style="margin:4px 0 10px;font-size:10.5px;color:var(--ink-500)">{{ $r[1] }}</p>
            @endif
            <div class="tag-row" style="margin-top:8px">
              @foreach($r[2] as $perm)
                @php $isSensitive = str_contains($perm, 'manage') || str_contains($perm, 'security') || str_contains($perm, 'publish'); @endphp
                <span class="tag" style="{{ $isSensitive ? 'background:var(--danger-bg);color:var(--danger-strong);border-color:var(--danger-border);font-weight:700' : '' }}">
                  {{ $perm }}
                </span>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>

      <div class="info-strip warning" style="margin-top:16px;margin-bottom:0">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
        <div class="copy">
          <strong>Health rule publish permissions are strictly segregated.</strong>
          <span>General content editors cannot unilaterally publish health-impacting rules to production mobile apps without clinical reviewer sign-off.</span>
        </div>
      </div>
    </div>
  @elseif($tab === 'matrix')
    {{-- Red Font Exception Alert Banner: Shows ONLY when someone tries to modify Super Admin access --}}
    <div id="permissionDeclinedAlert" style="display: none; margin: 18px 20px 0; padding: 14px 18px; border-radius: 12px; background: #fef2f2; border: 1.5px solid #f87171; color: #dc2626; align-items: flex-start; gap: 12px;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2" style="flex-shrink:0;margin-top:2px"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
      <div>
        <strong id="permissionDeclinedTitle" style="font-size: 14px; display: block; color: #dc2626; font-weight: 800; letter-spacing: -0.2px;">Permission Declined</strong>
        <p id="permissionDeclinedMessage" style="margin: 3px 0 0; font-size: 12px; color: #dc2626; font-weight: 600; line-height: 1.45;">
          Super Admin has Access in Everything. No one can modify Super Admin Access.
        </p>
      </div>
    </div>

    {{-- Role Selector Card (Matching Reference Design) --}}
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--line-soft); background: var(--surface); display: flex; align-items: center; gap: 28px; flex-wrap: wrap;">
      <div style="min-width: 250px;">
        <label for="matrixRoleSelect" style="display: block; font-size: 11.5px; font-weight: 700; color: var(--ink-700); margin-bottom: 6px; letter-spacing: .2px;">Role</label>
        <div style="position: relative;">
          <select id="matrixRoleSelect" class="select" style="width: 100%; font-size: 13px; font-weight: 600; padding: 9px 34px 9px 12px; border: 1px solid var(--line); border-radius: 9px; background: #fff; appearance: none; cursor: pointer;" onchange="onRoleMatrixChange(this.value)">
            @foreach($allRoles as $r)
              <option value="{{ $r }}" {{ $selectedRole === $r ? 'selected' : '' }}>{{ $r }}</option>
            @endforeach
          </select>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--ink-500)" stroke-width="2.5" style="position: absolute; right: 12px; top: 12px; pointer-events: none;"><path d="M6 9l6 6 6-6"/></svg>
        </div>
      </div>
      <div style="flex: 1; min-width: 280px; border-left: 2px solid var(--line-soft); padding-left: 24px;">
        <h3 id="matrixRoleTitle" style="margin: 0; font-size: 16px; font-weight: 800; color: var(--ink-950); letter-spacing: -0.25px;">{{ $selectedRole }}</h3>
        <p id="matrixRoleDesc" style="margin: 4px 0 0; font-size: 12px; color: var(--ink-500); line-height: 1.45;">{{ $roleDescriptions[$selectedRole] ?? '' }}</p>
      </div>
    </div>

    {{-- Permission Matrix Table (9 Columns: MODULE, VIEW, CREATE, EDIT OWN, EDIT ALL, DELETE, ASSIGN, LINK, EXPORT) --}}
    <div class="table-wrap" style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 860px;">
        <thead>
          <tr style="background: var(--surface-subtle); border-bottom: 1px solid var(--line);">
            <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .6px; width: 280px;">MODULE</th>
            @foreach($matrixActions as $actKey => $actLabel)
              <th style="padding: 12px 10px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .6px; text-align: center; width: 85px;">
                {{ $actLabel }}
              </th>
            @endforeach
          </tr>
        </thead>
        <tbody id="matrixTableBody">
          @php $isSuper = ($selectedRole === \App\Models\Admin::ROLE_SUPER_ADMIN); @endphp
          @foreach($matrixModules as $modKey => $mod)
            <tr style="border-bottom: 1px solid var(--line-soft); transition: background .12s;">
              <td style="padding: 12px 20px;">
                <strong style="font-size: 13px; color: var(--ink-900); display: block; line-height: 1.3;">{{ $mod['name'] }}</strong>
                <span style="font-size: 11px; color: var(--ink-400); display: block; margin-top: 2px;">{{ $mod['category'] }}</span>
              </td>
              @foreach($matrixActions as $actKey => $actLabel)
                @php
                  $isGranted = $isSuper ? true : ($effectiveMatrix[$selectedRole][$modKey][$actKey] ?? false);
                @endphp
                <td style="padding: 12px 10px; text-align: center; vertical-align: middle;">
                  <label class="matrix-check-wrap {{ $isSuper ? 'disabled' : '' }}"
                         title="{{ $mod['name'] }} · {{ $actLabel }}"
                         onclick="handleMatrixCheckWrapClick(event, this, '{{ $modKey }}', '{{ $actKey }}')">
                    <input type="checkbox"
                           class="matrix-checkbox"
                           data-module="{{ $modKey }}"
                           data-action="{{ $actKey }}"
                           {{ $isGranted ? 'checked' : '' }}
                           onchange="onMatrixCheckboxToggle(this, '{{ $modKey }}', '{{ $actKey }}')">
                    <span class="matrix-check-box">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6L9 17l-5-5"/>
                      </svg>
                    </span>
                  </label>
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Footer Actions Bar --}}
    <div class="toolbar" style="padding: 16px 24px; border-top: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; background: var(--surface);">
      <div style="display: flex; align-items: center; gap: 8px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--ink-400)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span id="matrixStatusHelp" style="font-size: 11.5px; color: var(--ink-500);">
          Only Super Admin can control and assign role permissions. All changes apply system-wide immediately.
        </span>
      </div>
      <div style="display: flex; align-items: center; gap: 10px;">
        <button type="button" class="btn btn-secondary btn-sm" id="matrixResetBtn" onclick="resetRoleMatrix()">
          Reset to Defaults
        </button>
        <button type="button" class="btn btn-primary btn-sm" id="matrixSaveBtn" onclick="savePermissionMatrix()">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
          Save Permission Matrix
        </button>
      </div>
    </div>
  @elseif($tab === 'audit')
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Action</th>
            <th>Details</th>
            <th>Operator</th>
            <th>Status</th>
            <th>Timestamp</th>
          </tr>
        </thead>
        <tbody>
          @foreach($auditLogs as $log)
            <tr>
              <td><strong>{{ $log->action }}</strong></td>
              <td><span class="cell-sub" style="max-width:320px">{{ $log->detail }}</span></td>
              <td>{{ $log->user }}</td>
              <td>
                @php $st = strtolower($log->status); @endphp
                <span class="badge {{ $st }}">{{ $log->status }}</span>
              </td>
              <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @elseif($tab === 'logins')
    <div class="card-body">
      <div style="display:flex;flex-direction:column">
        @foreach($admins as $idx => $a)
          <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--line-soft)">
            <div style="width:34px;height:34px;border-radius:10px;background:var(--green-50);color:var(--green-700);display:grid;place-items:center;flex:none">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div style="flex:1">
              <strong style="font-size:12px">{{ $a->name }} ({{ $a->email }})</strong>
              <p style="margin:2px 0 0;font-size:10.5px;color:var(--ink-500)">
                Successful sign-in via Secure Admin Session · IP: 127.0.0.1
              </p>
            </div>
            <span class="badge verified">Verified</span>
            <span style="font-size:11px;color:var(--ink-400)">{{ $a->last_login_at ? $a->last_login_at->format('M d, H:i') : 'Active' }}</span>
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>

@push('scripts')
<script>
  const systemRoles = @json(array_map(fn($r) => $r[0], $roles));
  const roleDescriptions = @json($roleDescriptions ?? []);
  const matrixData = @json($effectiveMatrix ?? []);
  const superAdminRole = '{{ \App\Models\Admin::ROLE_SUPER_ADMIN }}';
  let currentMatrixRole = '{{ $selectedRole ?? \App\Models\Admin::ROLE_MANAGEMENT }}';

  function showPermissionDeclined(message) {
    const alertBox = document.getElementById('permissionDeclinedAlert');
    const msgEl = document.getElementById('permissionDeclinedMessage');
    if (alertBox && msgEl) {
      if (message) {
        msgEl.textContent = message;
      }
      alertBox.style.display = 'flex';
      alertBox.classList.remove('declined-pulse');
      void alertBox.offsetWidth; // trigger reflow
      alertBox.classList.add('declined-pulse');
      alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  function hidePermissionDeclined() {
    const alertBox = document.getElementById('permissionDeclinedAlert');
    if (alertBox) {
      alertBox.style.display = 'none';
    }
  }

  function onRoleMatrixChange(roleName) {
    currentMatrixRole = roleName;

    // Update title & description
    const titleEl = document.getElementById('matrixRoleTitle');
    const descEl = document.getElementById('matrixRoleDesc');
    if (titleEl) titleEl.textContent = roleName;
    if (descEl) descEl.textContent = roleDescriptions[roleName] || '';

    // Always hide the alert when switching/selecting a role - it only shows when someone tries to modify!
    hidePermissionDeclined();

    const checkboxes = document.querySelectorAll('.matrix-checkbox');

    if (roleName === superAdminRole) {
      checkboxes.forEach(cb => {
        cb.checked = true;
        cb.closest('.matrix-check-wrap')?.classList.add('disabled');
      });
    } else {
      const rolePerms = matrixData[roleName] || {};
      checkboxes.forEach(cb => {
        const mod = cb.dataset.module;
        const act = cb.dataset.action;
        const isGranted = !!(rolePerms[mod] && rolePerms[mod][act]);
        cb.checked = isGranted;
        cb.closest('.matrix-check-wrap')?.classList.remove('disabled');
      });
    }
  }

  function handleMatrixCheckWrapClick(event, labelEl, mod, act) {
    if (currentMatrixRole === superAdminRole) {
      event.preventDefault();
      event.stopPropagation();
      const cb = labelEl.querySelector('.matrix-checkbox');
      if (cb) {
        cb.checked = true;
      }
      showPermissionDeclined('Super Admin has Access in Everything. No one can modify Super Admin Access.');
      return false;
    }
  }

  function onMatrixCheckboxToggle(cb, mod, act) {
    if (currentMatrixRole === superAdminRole) {
      cb.checked = true;
      showPermissionDeclined('Super Admin has Access in Everything. No one can modify Super Admin Access.');
      return;
    }

    if (!matrixData[currentMatrixRole]) {
      matrixData[currentMatrixRole] = {};
    }
    if (!matrixData[currentMatrixRole][mod]) {
      matrixData[currentMatrixRole][mod] = {};
    }
    matrixData[currentMatrixRole][mod][act] = cb.checked;
  }

  async function savePermissionMatrix() {
    if (currentMatrixRole === superAdminRole) {
      showPermissionDeclined('Super Admin has Access in Everything. No one can modify Super Admin Access.');
      return;
    }

    const saveBtn = document.getElementById('matrixSaveBtn');
    const originalHtml = saveBtn ? saveBtn.innerHTML : '';
    if (saveBtn) {
      saveBtn.disabled = true;
      saveBtn.innerHTML = `
        <svg style="display:inline-block;vertical-align:middle;margin-right:6px;animation:spin 1s linear infinite" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <circle cx="12" cy="12" r="10" stroke-opacity="0.25" stroke="currentColor"/>
          <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor"/>
        </svg>
        Saving Matrix...
      `;
    }

    // Build permissions map from current state
    const permissions = {};
    document.querySelectorAll('.matrix-checkbox').forEach(cb => {
      const mod = cb.dataset.module;
      const act = cb.dataset.action;
      if (!permissions[mod]) {
        permissions[mod] = {};
      }
      permissions[mod][act] = cb.checked;
    });

    try {
      const res = await fetch('{{ route('admin.security.matrix.update') }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          role: currentMatrixRole,
          permissions: permissions
        })
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok || !data.success) {
        showPermissionDeclined(data.error || data.message || 'Permission Declined: Unauthorized action.');
        if (typeof showToast === 'function') {
          showToast(data.message || 'Permission Declined', 'danger');
        }
        return;
      }

      hidePermissionDeclined();
      if (data.matrix) {
        Object.assign(matrixData, data.matrix);
      }
      if (typeof showToast === 'function') {
        showToast(data.message || 'Permission matrix successfully updated.', 'success');
      }

    } catch (err) {
      console.error(err);
      showPermissionDeclined('Permission Declined: Network or server error prevented updating permissions.');
    } finally {
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalHtml;
      }
    }
  }

  async function resetRoleMatrix() {
    if (currentMatrixRole === superAdminRole) {
      showPermissionDeclined('Super Admin has Access in Everything. No one can modify Super Admin Access.');
      return;
    }

    if (!confirm(`Are you sure you want to reset all permissions for '${currentMatrixRole}' to system defaults?`)) {
      return;
    }

    const resetBtn = document.getElementById('matrixResetBtn');
    const originalHtml = resetBtn ? resetBtn.innerHTML : '';
    if (resetBtn) {
      resetBtn.disabled = true;
      resetBtn.textContent = 'Resetting...';
    }

    try {
      const res = await fetch('{{ route('admin.security.matrix.update') }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          role: currentMatrixRole,
          reset: true
        })
      });

      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data.success) {
        showPermissionDeclined(data.error || data.message || 'Permission Declined');
        return;
      }

      hidePermissionDeclined();
      if (data.matrix) {
        Object.assign(matrixData, data.matrix);
      }
      onRoleMatrixChange(currentMatrixRole);
      if (typeof showToast === 'function') {
        showToast(data.message || 'Permissions reset to system defaults.', 'success');
      }
    } catch (err) {
      console.error(err);
      showPermissionDeclined('Permission Declined: Unable to reset permissions.');
    } finally {
      if (resetBtn) {
        resetBtn.disabled = false;
        resetBtn.innerHTML = originalHtml;
      }
    }
  }

  function updateRoleDescription(selectEl, descElId) {
    const descEl = document.getElementById(descElId);
    if (descEl && roleDescriptions[selectEl.value]) {
      descEl.textContent = roleDescriptions[selectEl.value];
    }
  }

  async function submitAdminForm(formId, submitBtn) {
    const form = document.getElementById(formId);
    if (!form) return;

    // Clear previous errors & highlights
    form.querySelectorAll('.modal-form-errors').forEach(el => el.remove());
    form.querySelectorAll('.input, .select').forEach(el => {
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
          errorList.push('An unexpected error occurred. Please verify your credentials and try again.');
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
        showToast(data.message || 'Operation successful', 'success');
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

  function openAddAdminModal() {
    const defaultRole = 'Product Manager';
    const rolesHtml = systemRoles.map(role => 
      `<option value="${role}" ${role === defaultRole ? 'selected' : ''}>${role}</option>`
    ).join('');

    openModal({
      title: 'Add New Admin / Manager',
      subtitle: 'Provision administrative access to the ScanWell control center',
      size: 'wide',
      body: `
        <form id="createAdminForm" method="POST" action="{{ route('admin.security.admins.store') }}" novalidate onsubmit="event.preventDefault(); submitAdminForm('createAdminForm', document.getElementById('btnSubmitAddAdmin'))">
          @csrf
          <div class="modal-form-errors-container"></div>
          <div class="form-grid">
            <div class="field">
              <label>Full Name *</label>
              <input name="name" class="input" required placeholder="e.g. Farhan Ahmed" autofocus>
            </div>
            <div class="field">
              <label>Corporate Email *</label>
              <input name="email" type="email" class="input" required placeholder="e.g. farhan@scanwell.app">
            </div>
            <div class="field">
              <label>Initial Password * (minimum 6 characters)</label>
              <input name="password" type="password" class="input" required minlength="6" placeholder="Enter secure password...">
            </div>
            <div class="field">
              <label>Administrative Role *</label>
              <select name="role" class="select" required onchange="updateRoleDescription(this, 'addRoleDesc')">
                ${rolesHtml}
              </select>
              <div id="addRoleDesc" style="font-size:11px;color:var(--ink-600);background:var(--surface-subtle);border:1px solid var(--line-soft);border-radius:6px;padding:6px 8px;margin-top:6px;line-height:1.35;">
                ${roleDescriptions[defaultRole]}
              </div>
            </div>
            <div class="field full">
              <label>Account Status *</label>
              <select name="status" class="select" required>
                <option value="Active" selected>Active — Full clearance</option>
                <option value="Suspended">Suspended — Login restricted</option>
              </select>
            </div>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" id="btnSubmitAddAdmin" class="btn btn-primary" onclick="submitAdminForm('createAdminForm', this)">Create Staff Account</button>
      `
    });
  }

  function openEditAdminModal(admin) {
    const rolesHtml = systemRoles.map(role => 
      `<option value="${role}" ${role === admin.role ? 'selected' : ''}>${role}</option>`
    ).join('');

    openModal({
      title: `Edit Staff: ${admin.name}`,
      subtitle: `Assigned Role: ${admin.role}`,
      size: 'wide',
      body: `
        <form id="editAdminForm" method="POST" action="{{ url('admin/security/admins') }}/${admin.id}" novalidate onsubmit="event.preventDefault(); submitAdminForm('editAdminForm', document.getElementById('btnSubmitEditAdmin'))">
          @csrf
          @method('PUT')
          <div class="modal-form-errors-container"></div>
          <div class="form-grid">
            <div class="field">
              <label>Full Name *</label>
              <input name="name" class="input" value="${admin.name || ''}" required autofocus>
            </div>
            <div class="field">
              <label>Corporate Email *</label>
              <input name="email" type="email" class="input" value="${admin.email || ''}" required>
            </div>
            <div class="field">
              <label>New Password (leave blank to keep current)</label>
              <input name="password" type="password" class="input" minlength="6" placeholder="••••••••">
            </div>
            <div class="field">
              <label>Administrative Role *</label>
              <select name="role" class="select" required onchange="updateRoleDescription(this, 'editRoleDesc')">
                ${rolesHtml}
              </select>
              <div id="editRoleDesc" style="font-size:11px;color:var(--ink-600);background:var(--surface-subtle);border:1px solid var(--line-soft);border-radius:6px;padding:6px 8px;margin-top:6px;line-height:1.35;">
                ${roleDescriptions[admin.role] || ''}
              </div>
            </div>
            <div class="field full">
              <label>Account Status *</label>
              <select name="status" class="select" required>
                <option value="Active" ${admin.status === 'Active' ? 'selected' : ''}>Active — Full clearance</option>
                <option value="Suspended" ${admin.status === 'Suspended' ? 'selected' : ''}>Suspended — Login restricted</option>
              </select>
            </div>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" id="btnSubmitEditAdmin" class="btn btn-primary" onclick="submitAdminForm('editAdminForm', this)">Save Changes</button>
      `
    });
  }

  function openDeleteAdminModal(id, name, role) {
    openModal({
      title: `Remove Staff Member: ${name}`,
      subtitle: 'Security & Access Revocation',
      body: `
        <form id="deleteAdminForm" method="POST" action="{{ url('admin/security/admins') }}/${id}">
          @csrf
          @method('DELETE')
          <p style="margin:0;font-size:13.5px;line-height:1.5">
            Are you sure you want to permanently remove <strong>${name}</strong> (${role}) from the administrative staff?
          </p>
          <div class="info-strip warning" style="margin-top:14px;background:var(--danger-bg);border-color:var(--danger-border);color:var(--danger-strong)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3L2 20h20L12 3z"/><path d="M12 9v4M12 17h.01"/></svg>
            <div class="copy" style="font-size:12px">
              <strong>Immediate Revocation:</strong>
              <span>This account will be permanently deleted and all active sessions will be terminated immediately.</span>
            </div>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="document.getElementById('deleteAdminForm').submit()">Remove Staff</button>
      `
    });
  }

  @if($errors->any() && ($errors->has('name') || $errors->has('email') || $errors->has('password') || $errors->has('role') || $errors->has('status')))
  document.addEventListener('DOMContentLoaded', function() {
    const pageAlert = document.getElementById('pageValidationAlert');
    if (pageAlert) {
      pageAlert.style.display = 'none';
    }

    openAddAdminModal();

    const form = document.getElementById('createAdminForm');
    if (form) {
      @if(old('name'))
        const nameInput = form.querySelector('[name="name"]');
        if (nameInput) nameInput.value = @json(old('name'));
      @endif
      @if(old('email'))
        const emailInput = form.querySelector('[name="email"]');
        if (emailInput) emailInput.value = @json(old('email'));
      @endif
      @if(old('role'))
        const roleSelect = form.querySelector('[name="role"]');
        if (roleSelect) {
          roleSelect.value = @json(old('role'));
          updateRoleDescription(roleSelect, 'addRoleDesc');
        }
      @endif
      @if(old('status'))
        const statusSelect = form.querySelector('[name="status"]');
        if (statusSelect) statusSelect.value = @json(old('status'));
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

