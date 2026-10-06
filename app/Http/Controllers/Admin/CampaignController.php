<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\MasterData;
use App\Http\Requests\Admin\CampaignCustomRequest;
use App\Models\AuditLog;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'All');
        $query = Campaign::query();

        if ($status !== 'All') {
            $query->where('status', $status);
        }

        $campaigns = $query->latest('start_at')->get();
        $activeCampaigns = Campaign::active()->get();
        $totalImpressions = $activeCampaigns->sum('impressions');
        $totalClicks = $activeCampaigns->sum('clicks');
        $avgCtr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 1) : 0.0;
        $countries = MasterData::where('type', 'Countries')->pluck('name');

        return view('admin.ads.index', compact(
            'campaigns',
            'status',
            'activeCampaigns',
            'totalImpressions',
            'totalClicks',
            'avgCtr',
            'countries'
        ));
    }

    public function show(Campaign $campaign)
    {
        return response()->json($campaign);
    }

    public function store(CampaignCustomRequest $request)
    {
        $campaign = Campaign::create($request->validated());

        AuditLog::record(
            action: 'Created campaign',
            detail: "{$campaign->name} ({$campaign->type})",
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: $campaign->status
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Campaign '{$campaign->name}' created.",
                'campaign' => $campaign,
            ]);
        }

        return redirect()->back()->with('success', "Campaign '{$campaign->name}' created.");
    }

    public function update(CampaignCustomRequest $request, Campaign $campaign)
    {
        $campaign->update($request->validated());

        AuditLog::record(
            action: 'Updated campaign',
            detail: "{$campaign->name} ({$campaign->status})",
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: $campaign->status
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Campaign '{$campaign->name}' updated.",
                'campaign' => $campaign,
            ]);
        }

        return redirect()->back()->with('success', "Campaign '{$campaign->name}' updated.");
    }

    public function toggle(Campaign $campaign)
    {
        $newStatus = $campaign->status === 'Active' ? 'Paused' : 'Active';
        $campaign->update(['status' => $newStatus]);

        AuditLog::record(
            action: $newStatus === 'Active' ? 'Activated campaign' : 'Paused campaign',
            detail: $campaign->name,
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: $newStatus
        );

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => "Campaign '{$campaign->name}' is now {$newStatus}.",
        ]);
    }
}
