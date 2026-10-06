<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductDuplicate;
use App\Models\AuditLog;

class DuplicateController extends Controller
{
    public function index()
    {
        $duplicates = ProductDuplicate::all();
        $potentialCount = $duplicates->count();
        $highMatchCount = $duplicates->where('match_percentage', '>=', 90)->count();
        $reviewCount = $duplicates->where('status', 'Review')->count();
        $resolvedCount = $duplicates->where('status', 'Resolved')->count();

        return view('admin.products.duplicates', compact(
            'duplicates',
            'potentialCount',
            'highMatchCount',
            'reviewCount',
            'resolvedCount'
        ));
    }

    public function resolve(Request $request, ProductDuplicate $duplicate)
    {
        $action = $request->input('action', 'Resolved');
        $duplicate->update(['status' => $action]);

        AuditLog::record(
            action: "Resolved duplicate product check",
            detail: "{$duplicate->name} ({$duplicate->id}) marked as {$action}",
            user: auth('admin')->user()?->name ?? 'Admin Staff'
        );

        return redirect()->back()->with('success', "Duplicate {$duplicate->id} marked as {$action}.");
    }
}
