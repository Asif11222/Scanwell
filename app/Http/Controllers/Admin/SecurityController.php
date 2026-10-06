<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\AuditLog;

use App\Models\AppControl;
use App\Http\Requests\Admin\AdminCustomRequest;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'admins');
        $admins = Admin::all();
        $auditLogs = AuditLog::latest('created_at')->take(50)->get();

        $matrixModules = Admin::MATRIX_MODULES;
        $matrixActions = Admin::MATRIX_ACTIONS;
        $roleDescriptions = Admin::ROLE_DESCRIPTIONS;
        $allRoles = Admin::ALL_ROLES;
        $effectiveMatrix = Admin::getEffectiveMatrix();
        $selectedRole = $request->input('role', Admin::ROLE_MANAGEMENT);

        $roles = [
            ['Super Admin', '', [
                'security.manage',
                'app_control.manage',
                'products.view',
                'products.manage',
                'health.view',
                'health.manage',
                'marketing.view',
                'marketing.manage',
                'submissions.view',
                'submissions.manage',
                'corrections.view',
                'corrections.manage',
                'duplicates.manage',
                'users.view',
                'users.manage',
                'master_data.manage',
            ]],
            ['Management', '', [
                'dashboard.view',
                'report.view',
                'report.export',
                'notifications.view',
                'clients.manage',
                'inquiries.manage',
                'orders.manage',
            ]],
            ['Product Manager', '', [
                'dashboard.view',
                'products.view',
                'products.manage',
                'duplicates.manage',
                'master_data.manage',
                'submissions.view',
                'corrections.view',
                'users.view',
            ]],
            ['Health Content Reviewer', '', [
                'dashboard.view',
                'health.view',
                'health.manage',
                'products.view',
                'master_data.view',
                'users.view',
            ]],
            ['Submission Reviewer', '', [
                'dashboard.view',
                'submissions.view',
                'submissions.manage',
                'corrections.view',
                'corrections.manage',
                'duplicates.manage',
                'products.view',
                'products.edit',
                'users.view',
            ]],
            ['Marketing Manager', '', [
                'dashboard.view',
                'marketing.view',
                'marketing.manage',
                'products.view',
                'users.view',
            ]],
            ['Support Viewer', '', [
                'dashboard.view',
                'products.view',
                'submissions.view',
                'corrections.view',
                'users.view',
                'audit.view',
            ]],
        ];

        return view('admin.security.index', compact(
            'tab',
            'admins',
            'auditLogs',
            'roles',
            'matrixModules',
            'matrixActions',
            'roleDescriptions',
            'allRoles',
            'effectiveMatrix',
            'selectedRole'
        ));
    }

    public function updateMatrix(Request $request)
    {
        $currentAuth = auth('admin')->user();

        // Safety 1: Only Super Admin can control and give role access
        if (! $currentAuth || ! $currentAuth->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Permission Declined',
                'error' => 'Permission Declined: Only Super Admin can control and configure role permissions.',
            ], 403);
        }

        $targetRole = $request->input('role');

        // Safety 2: Super Admin access is permanent and immutable
        if ($targetRole === Admin::ROLE_SUPER_ADMIN) {
            AuditLog::record(
                action: 'Unauthorized Permission Modification Attempt',
                detail: "Attempted to modify Super Admin permissions - Rejected with Permission Declined",
                user: $currentAuth->name,
                status: 'Denied'
            );

            return response()->json([
                'success' => false,
                'message' => 'Permission Declined',
                'error' => 'Permission Declined: Super Admin has full access to everything. Super Admin permissions are permanent and cannot be modified.',
            ], 403);
        }

        if (! in_array($targetRole, Admin::ALL_ROLES, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid role selected.',
            ], 422);
        }

        $stored = AppControl::getVal('role_permission_matrix', []);
        if (is_string($stored)) {
            $stored = json_decode($stored, true) ?: [];
        }
        if (! is_array($stored)) {
            $stored = [];
        }

        // Handle reset to defaults
        if ($request->boolean('reset')) {
            unset($stored[$targetRole]);
            AppControl::setVal('role_permission_matrix', $stored, 'json', 'Role-to-module permission matrix configuration');

            AuditLog::record(
                action: 'Reset Permission Matrix',
                detail: "Reset permissions for role '{$targetRole}' to system defaults",
                user: $currentAuth->name,
                status: 'Success'
            );

            return response()->json([
                'success' => true,
                'message' => "Permissions for role '{$targetRole}' successfully reset to default.",
                'matrix' => Admin::getEffectiveMatrix(),
            ]);
        }

        $submittedPermissions = $request->input('permissions', []);
        $moduleKeys = array_keys(Admin::MATRIX_MODULES);
        $actionKeys = array_keys(Admin::MATRIX_ACTIONS);

        $newRolePermissions = [];
        foreach ($moduleKeys as $m) {
            $newRolePermissions[$m] = [];
            foreach ($actionKeys as $a) {
                $newRolePermissions[$m][$a] = ! empty($submittedPermissions[$m][$a]);
            }
        }

        $stored[$targetRole] = $newRolePermissions;
        unset($stored[Admin::ROLE_SUPER_ADMIN]); // Guarantee Super Admin is never altered

        AppControl::setVal('role_permission_matrix', $stored, 'json', 'Role-to-module permission matrix configuration');

        AuditLog::record(
            action: 'Updated Permission Matrix',
            detail: "Updated access permissions for role '{$targetRole}'",
            user: $currentAuth->name,
            status: 'Success'
        );

        return response()->json([
            'success' => true,
            'message' => "Permissions for role '{$targetRole}' successfully updated.",
            'matrix' => Admin::getEffectiveMatrix(),
        ]);
    }

    public function storeAdmin(AdminCustomRequest $request)
    {
        $validated = $request->validated();

        $admin = Admin::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => $validated['status'],
        ]);

        $currentAdminName = auth('admin')->user()?->name ?? 'System Admin';

        AuditLog::record(
            action: 'Created admin staff',
            detail: "Created '{$admin->name}' ({$admin->email}) as {$admin->role}",
            user: $currentAdminName,
            status: 'Success'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Administrator '{$admin->name}' successfully created.",
                'admin' => $admin,
            ]);
        }

        return redirect()->route('admin.security.index', ['tab' => 'admins'])
            ->with('success', "Administrator '{$admin->name}' ({$admin->role}) created successfully.");
    }

    public function updateAdmin(AdminCustomRequest $request, Admin $admin)
    {
        $validated = $request->validated();
        $currentAuth = auth('admin')->user();

        // Safety guardrail: Self-status changes
        if ($currentAuth && $currentAuth->id === $admin->id && $validated['status'] !== 'Active') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot suspend your own active administrator account.',
                ], 422);
            }
            return redirect()->back()->withErrors(['status' => 'You cannot suspend your own active administrator account.']);
        }

        $data = [
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $admin->update($data);

        AuditLog::record(
            action: 'Updated admin staff',
            detail: "Updated '{$admin->name}' ({$admin->email}) - Role: {$admin->role}, Status: {$admin->status}",
            user: $currentAuth?->name ?? 'System Admin',
            status: 'Success'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Administrator '{$admin->name}' updated successfully.",
                'admin' => $admin,
            ]);
        }

        return redirect()->route('admin.security.index', ['tab' => 'admins'])
            ->with('success', "Administrator '{$admin->name}' updated successfully.");
    }

    public function destroyAdmin(Request $request, Admin $admin)
    {
        $currentAuth = auth('admin')->user();

        // Safety guardrail 1: Cannot delete own account
        if ($currentAuth && $currentAuth->id === $admin->id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot remove your own active administrator account.',
                ], 422);
            }
            return redirect()->back()->withErrors(['delete' => 'You cannot remove your own active administrator account.']);
        }

        // Safety guardrail 2: Cannot delete the last remaining Super Admin
        if ($admin->role === 'Super Admin') {
            $superAdminsCount = Admin::where('role', 'Super Admin')->where('id', '!=', $admin->id)->count();
            if ($superAdminsCount < 1) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot remove the last remaining Super Admin.',
                    ], 422);
                }
                return redirect()->back()->withErrors(['delete' => 'Cannot remove the last remaining Super Admin.']);
            }
        }

        $name = $admin->name;
        $email = $admin->email;
        $role = $admin->role;

        $admin->delete();

        AuditLog::record(
            action: 'Deleted admin staff',
            detail: "Deleted '{$name}' ({$email}) - Role: {$role}",
            user: $currentAuth?->name ?? 'System Admin',
            status: 'Deleted'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Administrator '{$name}' removed from the system.",
            ]);
        }

        return redirect()->route('admin.security.index', ['tab' => 'admins'])
            ->with('success', "Administrator '{$name}' removed from the system.");
    }
}
