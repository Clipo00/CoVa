<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Livewire;

use App\Modules\Marketplace\Models\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationBell extends Component
{
    public function getUnreadCountProperty(): int
    {
        /** @var \App\Modules\Auth\Models\User $user */
        $user = auth()->user();

        if ($user === null) {
            return 0;
        }

        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function getLatestNotificationsProperty()
    {
        /** @var \App\Modules\Auth\Models\User $user */
        $user = auth()->user();

        if ($user === null) {
            return collect();
        }

        return Notification::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();
    }

    public function markAsRead(int $id): void
    {
        /** @var \App\Modules\Auth\Models\User $user */
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->update(['read_at' => now()]);
    }

    public function markAllAsRead(): void
    {
        /** @var \App\Modules\Auth\Models\User $user */
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Refresh the component when a new notification is broadcast.
     */
    protected function getListeners(): array
    {
        return [
            'notification-created' => '$refresh',
        ];
    }

    public function render(): View
    {
        return view('marketplace::livewire.notification-bell', [
            'unreadCount' => $this->unreadCount,
            'latestNotifications' => $this->latestNotifications,
        ]);
    }
}
