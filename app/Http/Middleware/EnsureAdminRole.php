<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $admin = auth('admin')->user();

        if (!$admin || !$admin->hasRole($roles)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Forbidden',
                    'message' => 'Your administrative role does not have clearance for this operation.',
                ], 403);
            }

            abort(403, 'Unauthorized action. Your role (' . ($admin?->role ?? 'Unauthenticated') . ') cannot access this resource.');
        }

        return $next($request);
    }
}
