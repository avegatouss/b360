<?php

namespace Modules\Eshop360\Http\Controllers\Notification;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotificationController extends Controller
{
    /**
     * Paginated list of all notifications for the current user.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('eshop360::notifications.index', compact('notifications'));
    }

    /**
     * JSON response with unread count (for AJAX polling).
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        // Redirect to the notification URL if available
        $url = $notification->data['url'] ?? null;

        if ($url && $url !== '#') {
            return redirect($url);
        }

        return redirect()->back()->with('status', 'Notification marquee comme lue.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('status', 'Toutes les notifications ont ete marquees comme lues.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $request->user()
            ->notifications()
            ->findOrFail($id)
            ->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('status', 'Notification supprimee.');
    }
}
