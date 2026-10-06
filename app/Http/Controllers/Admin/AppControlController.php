<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppControl;
use App\Http\Requests\Admin\AppControlCustomRequest;
use App\Models\AuditLog;

class AppControlController extends Controller
{
    public function index()
    {
        $controls = AppControl::all()->keyBy('key');

        return view('admin.app-control.index', compact('controls'));
    }

    public function update(AppControlCustomRequest $request)
    {
        $key = $request->input('key');
        $value = $request->input('value');
        $type = $request->input('type', 'string');

        AppControl::setVal($key, $value, $type);

        AuditLog::record(
            action: 'Changed app control',
            detail: "{$key} set to {$value}",
            user: auth('admin')->user()?->name ?? 'Admin Staff'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "App control '{$key}' updated.",
            ]);
        }

        return redirect()->back()->with('success', "Updated '{$key}'.");
    }

    public function toggle(Request $request)
    {
        $key = $request->input('key');
        $current = AppControl::getVal($key, false);
        $next = ! $current;

        AppControl::setVal($key, $next, 'boolean');

        AuditLog::record(
            action: 'Toggled app control feature flag',
            detail: "{$key} changed to " . ($next ? 'Enabled' : 'Disabled'),
            user: auth('admin')->user()?->name ?? 'Admin Staff'
        );

        return response()->json([
            'success' => true,
            'enabled' => $next,
            'message' => ucwords(preg_replace('/([A-Z])/', ' $1', $key)) . ' ' . ($next ? 'enabled' : 'disabled') . '.',
        ]);
    }
}
