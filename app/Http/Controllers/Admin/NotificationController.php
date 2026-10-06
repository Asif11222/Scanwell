<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Http\Requests\Admin\NotificationCustomRequest;
use App\Models\AuditLog;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::latest('created_at')->get();

        return view('admin.notifications.index', compact('notifications'));
    }

    public function show(Notification $notification)
    {
        return response()->json($notification);
    }

    public function store(NotificationCustomRequest $request)
    {
        $notification = Notification::create($request->validated());

        AuditLog::record(
            action: 'Scheduled notification',
            detail: $notification->title,
            user: auth('admin')->user()?->name ?? 'Admin Staff',
            status: $notification->status
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Push notification '{$notification->title}' configured.",
                'notification' => $notification,
            ]);
        }

        return redirect()->back()->with('success', "Notification '{$notification->title}' scheduled.");
    }
}
