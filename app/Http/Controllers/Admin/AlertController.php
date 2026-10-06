<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PersonalizedAlert;
use App\Models\HealthConcern;
use App\Models\AuditLog;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = PersonalizedAlert::orderBy('priority', 'asc')->get();
        $concerns = HealthConcern::pluck('name');
        $previewAlert = $alerts->first();

        return view('admin.health.alerts', compact('alerts', 'concerns', 'previewAlert'));
    }

    public function show(PersonalizedAlert $alert)
    {
        return response()->json($alert);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condition' => 'required|string',
            'trigger' => 'required|string',
            'severity' => 'required|in:Red,Yellow,Green',
            'title' => 'required|string|max:120',
            'message' => 'required|string|max:500',
            'recommendation' => 'nullable|string|max:500',
            'priority' => 'required|integer|between:1,20',
            'status' => 'required|in:Active,Draft',
            'destination' => 'nullable|string|max:100',
        ]);

        $alert = PersonalizedAlert::create($validated);

        AuditLog::record(
            action: 'Created personalized alert',
            detail: "{$alert->condition} ({$alert->title})",
            user: auth('admin')->user()?->name ?? 'Admin Staff'
        );

        return redirect()->back()->with('success', 'Personalized alert saved.');
    }
}
