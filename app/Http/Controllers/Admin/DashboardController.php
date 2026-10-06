<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\HealthRule;
use App\Models\ProductSubmission;
use App\Models\ProductCorrection;
use App\Models\ProductDuplicate;
use App\Models\Campaign;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\MasterData;
use App\Models\AppControl;

class DashboardController extends Controller
{
    public function index()
    {
        $usersCount = User::count();
        $scansToday = (int) env('SCANS_TODAY_COUNT', AppControl::getVal('scans_today', 0));
        $productsCount = Product::count();
        $pendingReviews = ProductSubmission::whereIn('status', ['Pending', 'Review'])->count();
        $flaggedProducts = Product::where('flags_count', '>=', 2)->count();
        $activeCampaigns = Campaign::where('status', 'Active')->count();
        $rulesAwaitingReview = HealthRule::where('status', 'Review')->count();
        $publishedRules = HealthRule::where('status', 'Published')->count();
        $pendingCorrections = ProductCorrection::where('status', 'Pending')->count();
        $unverifiedCount = Product::unverified()->where('status', '!=', 'Archived')->count();
        $duplicateCount = ProductDuplicate::where('status', '!=', 'Resolved')->count();

        // Scan activity trend data
        $scanTrend = $scansToday > 0
            ? [980, 1120, 1045, 1260, 1385, 1510, $scansToday]
            : [0, 0, 0, 0, 0, 0, 0];

        // Health signal distribution
        $redSignals = Product::where('flags_count', '>=', 3)->count();
        $yellowSignals = Product::where('flags_count', 2)->count();
        $greenSignals = Product::where('flags_count', '<=', 1)->count();

        // OCR accuracy metrics
        $submissionsCount = ProductSubmission::count();
        $approvedSubmissions = ProductSubmission::where('status', 'Approved')->count();
        $lowConfidenceCount = ProductSubmission::where('confidence', '<', 90)->count();

        // Recent audit activity
        $activities = AuditLog::latest('created_at')->take(5)->get();

        // Dynamic categories for quick product entry modal
        $categories = MasterData::where('type', 'Categories')
            ->where('is_active', true)
            ->pluck('name')
            ->merge(Category::where('is_active', true)->pluck('name'))
            ->unique()
            ->sort()
            ->values();

        return view('admin.dashboard', compact(
            'usersCount',
            'scansToday',
            'productsCount',
            'pendingReviews',
            'flaggedProducts',
            'activeCampaigns',
            'rulesAwaitingReview',
            'publishedRules',
            'pendingCorrections',
            'unverifiedCount',
            'duplicateCount',
            'scanTrend',
            'redSignals',
            'yellowSignals',
            'greenSignals',
            'submissionsCount',
            'approvedSubmissions',
            'lowConfidenceCount',
            'activities',
            'categories'
        ));
    }
}
