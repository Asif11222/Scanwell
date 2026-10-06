<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditLog;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->input('role', 'All');
        $query = User::query();

        if ($role !== 'All') {
            $query->where('role', $role);
        }

        $users = $query->latest('last_seen_at')->get();
        $contributors = User::where('role', 'Contributor')->get();
        $verifiedContributors = $contributors->where('verified', true)->count();
        $totalSubmissions = $contributors->sum('submissions_count');
        $suspendedCount = User::where('status', 'Suspended')->count();

        return view('admin.users.index', compact(
            'users',
            'role',
            'contributors',
            'verifiedContributors',
            'totalSubmissions',
            'suspendedCount'
        ));
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function toggleSuspend(User $user)
    {
        $newStatus = $user->status === 'Active' ? 'Suspended' : 'Active';
        $user->update(['status' => $newStatus]);

        AuditLog::record(
            action: ($newStatus === 'Suspended' ? 'Suspended account ' : 'Reactivated account ') . $user->email,
            detail: "User #{$user->id} ({$user->name})",
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: $newStatus
        );

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => "User {$user->name} is now {$newStatus}.",
        ]);
    }

    public function destroy(User $user)
    {
        $userName = $user->name;
        $userEmail = $user->email;
        $userId = $user->id;

        $user->delete();

        AuditLog::record(
            action: "Deleted user {$userEmail}",
            detail: "User #{$userId} ({$userName}) removed from users & contributors",
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: 'Deleted'
        );

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "User {$userName} has been removed successfully.",
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', "User {$userName} has been removed successfully.");
    }
}
