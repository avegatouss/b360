<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Notifications;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * V1.2-4 — Page de consultation des notifications Menuiserie360.
 *
 * Lit la table standard Laravel `notifications` (déjà migrée 2026-03-16).
 * Filtre sur les notifications dont le `type` commence par
 * "Modules\Menuiserie360\\" — c'est-à-dire toutes les classes
 * Notification définies sous le module (StockCritiqueNotification, et
 * d'autres à venir).
 *
 * Pas de cloche header pour V1.2 (la cloche du master layout est réservée
 * à Eshop360 via le hook layout_slots R-401-FIX — propagation Menuiserie360
 * dans ce slot est un lot ultérieur ADR-022 §extensions).
 */
final class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $notifications = $user->notifications()
            ->where('type', 'like', 'Modules\\\\Menuiserie360\\\\%')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $unread = $user->unreadNotifications()
            ->where('type', 'like', 'Modules\\\\Menuiserie360\\\\%')
            ->count();

        return view('menuiserie360::notifications.index', compact('notifications', 'unread'));
    }

    public function markRead(Request $request, string $slug, string $id): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification !== null && $notification->getAttribute('read_at') === null) {
            $notification->markAsRead();
        }

        return back()->with('success', 'Notification marquée comme lue.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        DB::table('notifications')
            ->where('notifiable_type', $user::class)
            ->where('notifiable_id', $user->getKey())
            ->whereNull('read_at')
            ->where('type', 'like', 'Modules\\Menuiserie360\\%')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Toutes les notifications marquées comme lues.');
    }
}
