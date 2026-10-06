@extends('layouts.admin')

@section('title', 'ScanWell Admin - Users & Contributors')
@section('breadcrumb', 'Users & Contributors')
@section('page_title', 'Users & Contributors')

@section('content')
<div class="page-head">
  <div>
    <h2>Users & Contributors</h2>
    <p>Review consumer activity, community contributor label submissions, and account status</p>
  </div>
</div>

<div class="grid grid-4" style="margin-bottom:18px">
  <div class="stat-card">
    <div class="stat-top">
      <div class="stat-icon">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <span class="delta flat">Total</span>
    </div>
    <div class="stat-value">{{ $users->count() }}</div>
    <div class="stat-label">Registered Accounts</div>
  </div>

  <div class="stat-card">
    <div class="stat-top">
      <div class="stat-icon blue">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
      </div>
      <span class="delta up">{{ $verifiedContributors }} verified</span>
    </div>
    <div class="stat-value">{{ $contributors->count() }}</div>
    <div class="stat-label">Community Contributors</div>
  </div>

  <div class="stat-card">
    <div class="stat-top">
      <div class="stat-icon purple">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8l-9 5-9-5 9-5 9 5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
      </div>
      <span class="delta up">Contributor Volume</span>
    </div>
    <div class="stat-value">{{ number_format($totalSubmissions) }}</div>
    <div class="stat-label">Label Submissions</div>
  </div>

  <div class="stat-card">
    <div class="stat-top">
      <div class="stat-icon amber">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
      </div>
      <span class="delta down">Policy</span>
    </div>
    <div class="stat-value">{{ $suspendedCount }}</div>
    <div class="stat-label">Suspended Accounts</div>
  </div>
</div>

<div class="card">
  <form method="GET" action="{{ route('admin.users.index') }}" class="toolbar">
    <select name="role" class="filter-select" onchange="this.form.submit()">
      <option value="All" {{ $role === 'All' ? 'selected' : '' }}>All Roles</option>
      <option value="Consumer" {{ $role === 'Consumer' ? 'selected' : '' }}>Consumer Only</option>
      <option value="Contributor" {{ $role === 'Contributor' ? 'selected' : '' }}>Contributor Only</option>
    </select>
    <div class="toolbar-spacer"></div>
    <span class="result-count">{{ $users->count() }} user accounts</span>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>User</th>
          <th>Role</th>
          <th>Verification</th>
          <th>Lifetime Scans</th>
          <th>Submissions</th>
          <th>Status</th>
          <th>Last Seen</th>
          <th style="text-align:right">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $u)
          <tr>
            <td>
              <div class="product-cell">
                <div class="avatar">{{ strtoupper(substr($u->name, 0, 2)) }}</div>
                <div>
                  <div class="cell-main">{{ $u->name }}</div>
                  <div class="cell-sub">{{ $u->email }}</div>
                </div>
              </div>
            </td>
            <td><strong>{{ $u->role }}</strong></td>
            <td>
              @if($u->verified)
                <span class="badge verified">Verified</span>
              @else
                <span class="badge pending">Standard</span>
              @endif
            </td>
            <td>{{ number_format($u->scans_count) }}</td>
            <td>{{ number_format($u->submissions_count) }}</td>
            <td>
              @php $st = strtolower($u->status); @endphp
              <span class="badge {{ $st }}">{{ $u->status }}</span>
            </td>
            <td>{{ $u->last_seen_at ? $u->last_seen_at->diffForHumans() : 'Recently' }}</td>
            <td style="text-align:right">
              <div class="actions" style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                <button type="button" class="btn btn-secondary btn-sm" onclick="openUserModal({{ $u->id }})">View Details</button>
                @if(auth('admin')->user()?->role === \App\Models\Admin::ROLE_SUPER_ADMIN)
                  <button type="button" class="mini-btn delete-btn" title="Delete User" style="color:var(--danger-strong)" onclick="openDeleteUserModal({{ $u->id }}, {{ json_encode($u->name) }}, {{ json_encode($u->email) }})">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="padding:32px;text-align:center;color:var(--ink-400)">No user accounts found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
  function openUserModal(id) {
    fetch(`{{ url('admin/users') }}/${id}`)
      .then(res => res.json())
      .then(u => {
        openModal({
          title: u.name,
          subtitle: `${u.role} · ${u.email}`,
          body: `
            <div class="grid grid-3" style="margin-bottom:16px">
              <div class="stat-card">
                <div class="stat-label">Lifetime Scans</div>
                <div class="stat-value" style="font-size:20px">${u.scans_count}</div>
              </div>
              <div class="stat-card">
                <div class="stat-label">Submissions</div>
                <div class="stat-value" style="font-size:20px">${u.submissions_count}</div>
              </div>
              <div class="stat-card">
                <div class="stat-label">Account Status</div>
                <div style="margin-top:8px">
                  <span class="badge ${u.status === 'Active' ? 'verified' : 'rejected'}">${u.status}</span>
                </div>
              </div>
            </div>
            <div class="notice">
              User joined: ${new Date(u.created_at).toLocaleDateString()}. Account permissions are governed by role settings.
            </div>
          `,
          footer: `
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
            @if(auth('admin')->user()?->role === \App\Models\Admin::ROLE_SUPER_ADMIN)
              <button type="button" class="btn ${u.status === 'Active' ? 'btn-secondary' : 'btn-primary'}" onclick="toggleUserSuspend(${u.id})">
                ${u.status === 'Active' ? 'Suspend Account' : 'Reactivate Account'}
              </button>
              <button type="button" class="btn btn-danger" onclick="openDeleteUserModal(${u.id}, ${JSON.stringify(u.name)}, ${JSON.stringify(u.email)})">
                Delete User
              </button>
            @endif
          `
        });
      });
  }

  function toggleUserSuspend(id) {
    fetch(`{{ url('admin/users') }}/${id}/toggle-suspend`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      }
    })
    .then(res => res.json())
    .then(data => {
      closeModal();
      showToast(data.message, 'success', 'User Status');
      setTimeout(() => location.reload(), 600);
    });
  }

  function openDeleteUserModal(id, name, email) {
    openModal({
      title: `Remove User: ${name}`,
      subtitle: `${email}`,
      body: `
        <form id="deleteUserForm" method="POST" action="{{ url('admin/users') }}/${id}">
          @csrf
          @method('DELETE')
          <p style="margin:0;font-size:13.5px;line-height:1.5">
            Are you sure you want to permanently delete user <strong>${name}</strong> (<span style="color:var(--ink-500)">${email}</span>)?
          </p>
          <div class="info-strip warning" style="margin-top:14px;background:var(--danger-bg);border-color:var(--danger-border);color:var(--danger-strong)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3L2 20h20L12 3z"/><path d="M12 9v4M12 17h.01"/></svg>
            <div class="copy" style="font-size:12px">
              <strong>Permanent Action:</strong>
              <span>This user account and associated profile data will be permanently removed. Submissions and corrections will be preserved with unlinked attribution.</span>
            </div>
          </div>
        </form>
      `,
      footer: `
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="document.getElementById('deleteUserForm').submit()">Delete User</button>
      `
    });
  }
</script>
@endpush
@endsection
