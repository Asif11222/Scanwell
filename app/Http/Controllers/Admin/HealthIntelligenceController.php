<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HealthRule;
use App\Models\HealthConcern;
use App\Models\MasterData;
use App\Http\Requests\Admin\HealthRuleCustomRequest;
use App\Models\AuditLog;

class HealthIntelligenceController extends Controller
{
    public function index()
    {
        $rules = HealthRule::orderBy('priority', 'desc')->get();
        $concerns = HealthConcern::where('active', true)->get();
        $nutrients = MasterData::where('type', 'Nutrients')->pluck('name');
        $additives = MasterData::where('type', 'Additives')->pluck('name');
        $units = MasterData::where('type', 'Units')->pluck('name');

        return view('admin.health.rules', compact('rules', 'concerns', 'nutrients', 'additives', 'units'));
    }

    public function show(HealthRule $rule)
    {
        return response()->json($rule);
    }

    public function store(HealthRuleCustomRequest $request)
    {
        $validated = $request->validated();
        $validated['version'] = '1.0';
        $validated['effective_date'] = $validated['status'] === 'Published' ? 'Effective now' : 'Not yet published';

        $rule = HealthRule::create($validated);

        AuditLog::record(
            action: ($rule->status === 'Published' ? 'Published health rule ' : 'Created health rule ') . "v{$rule->version}",
            detail: "{$rule->name} (Target: {$rule->target})",
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: $rule->status
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Health rule '{$rule->name}' successfully created.",
                'rule' => $rule,
            ]);
        }

        return redirect()->back()->with('success', "Health rule '{$rule->name}' created successfully.");
    }

    public function update(HealthRuleCustomRequest $request, HealthRule $rule)
    {
        $validated = $request->validated();

        // Increment minor version upon edits
        $currentVer = (float) $rule->version;
        $validated['version'] = number_format($currentVer + 0.1, 1);
        if ($validated['status'] === 'Published' && $rule->status !== 'Published') {
            $validated['effective_date'] = 'Effective now';
        }

        $rule->update($validated);

        AuditLog::record(
            action: ($rule->status === 'Published' ? 'Published health rule ' : 'Updated health rule ') . "v{$rule->version}",
            detail: "{$rule->name} (Status: {$rule->status})",
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: $rule->status
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Health rule '{$rule->name}' updated to v{$rule->version}.",
                'rule' => $rule,
            ]);
        }

        return redirect()->back()->with('success', "Health rule '{$rule->name}' saved as v{$rule->version}.");
    }

    public function destroy(HealthRule $rule)
    {
        $name = $rule->name;
        $target = $rule->target;
        $rule->delete();

        AuditLog::record(
            action: 'Deleted health rule',
            detail: "{$name} (Target: {$target})",
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: 'Deleted'
        );

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Health rule '{$name}' was deleted.",
            ]);
        }

        return redirect()->back()->with('success', "Health rule '{$name}' was deleted successfully.");
    }
}

