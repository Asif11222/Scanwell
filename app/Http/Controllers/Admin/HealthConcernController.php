<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HealthConcern;
use App\Http\Requests\Admin\HealthConcernCustomRequest;
use App\Models\AuditLog;

class HealthConcernController extends Controller
{
    public function index()
    {
        $concerns = HealthConcern::withCount('rules')->get();

        return view('admin.health.concerns', compact('concerns'));
    }

    public function show(HealthConcern $concern)
    {
        return response()->json($concern);
    }

    public function store(HealthConcernCustomRequest $request)
    {
        $validated = $request->validated();
        if (is_string($validated['mapped_nutrients'] ?? null)) {
            $validated['mapped_nutrients'] = array_map('trim', explode(',', $validated['mapped_nutrients']));
        }
        $validated['active'] = $request->boolean('active', true);

        $concern = HealthConcern::create($validated);

        AuditLog::record(
            action: 'Created health concern',
            detail: $concern->name,
            user: auth('admin')->user()?->name ?? 'Admin Staff'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Health concern '{$concern->name}' created.",
                'concern' => $concern,
            ]);
        }

        return redirect()->back()->with('success', "Health concern '{$concern->name}' created.");
    }

    public function update(HealthConcernCustomRequest $request, HealthConcern $concern)
    {
        $validated = $request->validated();
        if (is_string($validated['mapped_nutrients'] ?? null)) {
            $validated['mapped_nutrients'] = array_map('trim', explode(',', $validated['mapped_nutrients']));
        }
        $validated['active'] = $request->boolean('active');

        $concern->update($validated);

        AuditLog::record(
            action: 'Updated health concern',
            detail: $concern->name,
            user: auth('admin')->user()?->name ?? 'Admin Staff'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Health concern '{$concern->name}' updated.",
                'concern' => $concern,
            ]);
        }

        return redirect()->back()->with('success', "Health concern '{$concern->name}' updated.");
    }
}
