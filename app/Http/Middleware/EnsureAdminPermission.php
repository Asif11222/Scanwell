<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthenticated',
                    'message' => 'Authentication required.',
                ], 401);
            }
            return redirect()->route('admin.login');
        }

        $hasAccess = false;
        foreach ($permissions as $perm) {
            if ($admin->hasPermission($perm)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Forbidden',
                    'message' => 'Your administrative role does not have permission for this action.',
                ], 403);
            }

            abort(403, 'Unauthorized: Your role (' . $admin->role . ') is restricted from this feature.');
        }

        return $next($request);
    }
}
