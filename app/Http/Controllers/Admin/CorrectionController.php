<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductCorrection;
use App\Models\Product;
use App\Http\Requests\Admin\CorrectionReviewCustomRequest;
use App\Models\AuditLog;

class CorrectionController extends Controller
{
    public function index()
    {
        $corrections = ProductCorrection::latest('submitted_at')->get();
        $pending = $corrections->where('status', 'Pending')->count();
        $review = $corrections->where('status', 'Review')->count();
        $approved = $corrections->where('status', 'Approved')->count();
        $healthImpacting = $corrections->where('risk', 'Health-impacting')->count();

        return view('admin.products.corrections', compact(
            'corrections',
            'pending',
            'review',
            'approved',
            'healthImpacting'
        ));
    }

    public function pending()
    {
        $corrections = ProductCorrection::where('status', 'Pending')->latest('submitted_at')->get();

        return view('admin.products.pending-corrections', compact('corrections'));
    }

    public function show(ProductCorrection $correction)
    {
        return response()->json($correction);
    }

    public function review(CorrectionReviewCustomRequest $request, ProductCorrection $correction)
    {
        $validated = $request->validated();
        $status = $validated['status'];

        $correction->status = $status;
        $correction->save();

        // If approved, automatically patch the product!
        if ($status === 'Approved' && $correction->product) {
            $product = $correction->product;
            $field = strtolower(trim($correction->field));

            if ($field === 'ingredients') {
                $product->ingredients = $correction->to_value;
            } elseif ($field === 'serving size' || $field === 'serving_size') {
                $product->serving_size = $correction->to_value;
            } elseif ($field === 'manufacturer') {
                $product->manufacturer = $correction->to_value;
            } else {
                // Check if nutrient field (e.g. Sodium, Sugar, Calories, etc.)
                $nutrition = $product->nutrition ?? [];
                $nutrition[$correction->field] = $correction->to_value;
                $product->nutrition = $nutrition;
            }

            $product->save();
        }

        AuditLog::record(
            action: ($status === 'Approved' ? 'Approved correction ' : 'Reviewed correction ') . $correction->id,
            detail: "{$correction->product_name} · {$correction->field}: {$correction->from_value} → {$correction->to_value}",
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: $status
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Correction {$correction->id} moved to {$status}.",
            ]);
        }

        if ($status === 'Rejected') {
            return redirect()->back()
                ->with('status_type', 'rejected')
                ->with('status_title', 'Rejected')
                ->with('status_message', "Correction {$correction->id} is Rejected")
                ->with('rejected', "Correction {$correction->id} is Rejected");
        }

        if ($status === 'Review') {
            return redirect()->back()
                ->with('status_type', 'review')
                ->with('status_title', 'Under Review')
                ->with('status_message', "Correction {$correction->id} is Under Review")
                ->with('review', "Correction {$correction->id} is Under Review");
        }

        return redirect()->back()
            ->with('status_type', 'success')
            ->with('status_title', 'Success')
            ->with('status_message', "Correction {$correction->id} processed as {$status}.")
            ->with('success', "Correction {$correction->id} processed as {$status}.");
    }

    public function destroy(ProductCorrection $correction)
    {
        $id = $correction->id;
        $productName = $correction->product_name;
        $correction->delete();

        AuditLog::record(
            action: 'Deleted product correction',
            detail: "{$id} ({$productName})",
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: 'Deleted'
        );

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Correction {$id} was deleted.",
            ]);
        }

        return redirect()->back()->with('success', "Correction {$id} was deleted successfully.");
    }
}

