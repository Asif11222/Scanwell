<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductSubmission;
use App\Models\Product;
use App\Http\Requests\Admin\SubmissionReviewCustomRequest;
use App\Models\AuditLog;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'All');
        $query = ProductSubmission::query();

        if ($status !== 'All') {
            $query->where('status', $status);
        }

        $submissions = $query->latest('created_at')->get();

        return view('admin.products.submissions', compact('submissions', 'status'));
    }

    public function show(ProductSubmission $submission)
    {
        return response()->json($submission);
    }

    public function review(SubmissionReviewCustomRequest $request, ProductSubmission $submission)
    {
        $validated = $request->validated();
        $status = $validated['status'];
        $action = $validated['action_type'] ?? null;

        $submission->status = $status;
        if (! empty($validated['extracted_fields'])) {
            $submission->extracted_fields = $validated['extracted_fields'];
        }
        $submission->save();

        // If approved, ensure it gets added or published in Products table
        if ($status === 'Approved') {
            Product::updateOrCreate(
                ['barcode' => $submission->barcode],
                [
                    'name' => $submission->product_name,
                    'brand' => $submission->brand,
                    'category' => $submission->extracted_fields['Category'] ?? 'Packaged Food',
                    'status' => 'Published',
                    'verified' => true,
                    'source' => 'Verified OCR submission (' . $submission->id . ')',
                    'nutrition' => $submission->extracted_fields ?? [],
                ]
            );
        }

        AuditLog::record(
            action: ($status === 'Approved' ? 'Approved submission ' : ($status === 'Rejected' ? 'Rejected submission ' : 'Requested correction for submission ')) . $submission->id,
            detail: "{$submission->product_name} ({$submission->brand})",
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: $status
        );

        if ($request->ajax() || $request->wantsJson()) {
            $msg = match($status) {
                'Rejected' => "Submission {$submission->id} is Rejected",
                'Review' => "Submission {$submission->id} is Under Review",
                default => "Submission {$submission->id} processed as Approved.",
            };
            return response()->json([
                'success' => true,
                'status' => $status,
                'status_type' => match($status) {
                    'Rejected' => 'rejected',
                    'Review' => 'review',
                    default => 'success',
                },
                'title' => match($status) {
                    'Rejected' => 'Rejected',
                    'Review' => 'Under Review',
                    default => 'Success',
                },
                'message' => $msg,
            ]);
        }

        if ($status === 'Rejected') {
            return redirect()->back()
                ->with('status_type', 'rejected')
                ->with('status_title', 'Rejected')
                ->with('status_message', "Submission {$submission->id} is Rejected")
                ->with('rejected', "Submission {$submission->id} is Rejected");
        }

        if ($status === 'Review') {
            return redirect()->back()
                ->with('status_type', 'review')
                ->with('status_title', 'Under Review')
                ->with('status_message', "Submission {$submission->id} is Under Review")
                ->with('review', "Submission {$submission->id} is Under Review");
        }

        return redirect()->back()
            ->with('status_type', 'success')
            ->with('status_title', 'Success')
            ->with('status_message', "Submission {$submission->id} processed as Approved.")
            ->with('success', "Submission {$submission->id} processed as Approved.");
    }
}
