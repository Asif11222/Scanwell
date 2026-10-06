<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginCustomRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the administrative login portal.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle administrative login authentication using custom validation.
     */
    public function login(AdminLoginCustomRequest $request): RedirectResponse
    {
        $credentials = [
            'email' => strtolower(trim((string) $request->input('email'))),
            'password' => (string) $request->input('password'),
        ];
        $remember = (bool) $request->input('remember', false);

        if (!Auth::guard('admin')->attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Incorrect email or password',
                ]);
        }

        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        // Double check status safeguard
        if (strtolower($admin->status) !== 'active') {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors([
                'email' => 'Access Denied: This administrator account has been restricted by Security Compliance.',
            ]);
        }

        $admin->update(['last_login_at' => now()]);

        AuditLog::record(
            action: 'Admin Login',
            detail: "Administrator {$admin->name} ({$admin->role}) signed in successfully.",
            user: $admin->name
        );

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', "Welcome back, {$admin->name}!");
    }

    /**
     * Terminate the administrative session.
     */
    public function logout(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            AuditLog::record(
                action: 'Admin Logout',
                detail: "Administrator {$admin->name} signed out.",
                user: $admin->name
            );
        }

        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('info', 'You have been securely signed out.');
    }
}
