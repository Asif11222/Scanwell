<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppContent;
use App\Http\Requests\Admin\AppContentCustomRequest;
use App\Models\AuditLog;

class AppContentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'All');
        $query = AppContent::query();

        if ($status !== 'All') {
            $query->where('status', $status);
        }

        $entries = $query->latest('updated_at')->get();

        return view('admin.content.index', compact('entries', 'status'));
    }

    public function show(AppContent $content)
    {
        return response()->json($content);
    }

    public function store(AppContentCustomRequest $request)
    {
        $validated = $request->validated();
        $validated['editor'] = auth('admin')->user()?->name ?? 'Admin Staff';

        $entry = AppContent::create($validated);

        AuditLog::record(
            action: 'Created app content',
            detail: "{$entry->content_key} ({$entry->locale})",
            user: $entry->editor,
            status: $entry->status
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Content key '{$entry->content_key}' created.",
                'content' => $entry,
            ]);
        }

        return redirect()->back()->with('success', "Content entry '{$entry->content_key}' saved.");
    }

    public function update(AppContentCustomRequest $request, AppContent $content)
    {
        $validated = $request->validated();
        $validated['editor'] = auth('admin')->user()?->name ?? 'Admin Staff';

        $content->update($validated);

        AuditLog::record(
            action: 'Updated app content',
            detail: "{$content->content_key} ({$content->locale})",
            user: $content->editor,
            status: $content->status
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Content key '{$content->content_key}' updated.",
                'content' => $content,
            ]);
        }

        return redirect()->back()->with('success', "Content entry '{$content->content_key}' updated.");
    }
}
