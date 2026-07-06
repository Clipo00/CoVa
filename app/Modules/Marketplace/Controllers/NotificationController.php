<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Marketplace\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        $unreadCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view('marketplace::notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markRead(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->update(['read_at' => now()]);

        return redirect()->back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back();
    }
}
