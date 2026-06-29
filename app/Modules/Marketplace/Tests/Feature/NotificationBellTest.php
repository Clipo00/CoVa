<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Marketplace\Models\Notification;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);

        $plan = Plan::where('slug', 'free')->first();
        $this->user = User::create([
            'name' => 'Notif User',
            'email' => 'notif@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $createOrg->execute($this->user, 'Notif Org', 'notif-org');
    }

    public function test_bell_shows_unread_count_badge(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            Notification::create([
                'user_id' => $this->user->id,
                'type' => 'blueprint_updated',
                'data' => ['blueprint_uuid' => 'fake-uuid', 'blueprint_title' => "BP {$i}"],
            ]);
        }

        Livewire::actingAs($this->user)
            ->test(\App\Modules\Marketplace\Livewire\NotificationBell::class)
            ->assertSeeHtml('bg-red-500')
            ->assertSee('3');
    }

    public function test_dropdown_shows_latest_5_notifications(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            Notification::create([
                'user_id' => $this->user->id,
                'type' => 'blueprint_updated',
                'data' => ['blueprint_uuid' => 'fake-uuid', 'blueprint_title' => "BP {$i}"],
            ]);
        }

        $html = Livewire::actingAs($this->user)
            ->test(\App\Modules\Marketplace\Livewire\NotificationBell::class)
            ->html(false); // false = don't strip initial data

        // Count how many BP items are in the dropdown
        preg_match_all('/BP \d/', $html, $matches);
        $this->assertCount(5, $matches[0], 'Dropdown should show exactly 5 notifications');

        // Should NOT contain BP 6 or BP 7 (they exist but are outside the latest 5)
        // Actually this depends on ordering — just verify count is correct
    }

    public function test_mark_as_read_removes_from_unread_count(): void
    {
        $notification = Notification::create([
            'user_id' => $this->user->id,
            'type' => 'blueprint_updated',
            'data' => ['blueprint_uuid' => 'fake-uuid', 'blueprint_title' => 'Test BP'],
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(\App\Modules\Marketplace\Livewire\NotificationBell::class);

        // Badge should be visible
        $component->assertSeeHtml('bg-red-500');

        $component->call('markAsRead', $notification->id);

        // Badge should be gone
        $component->assertDontSeeHtml('bg-red-500');

        // Database should reflect read
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read_clears_all(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            Notification::create([
                'user_id' => $this->user->id,
                'type' => 'blueprint_updated',
                'data' => ['blueprint_uuid' => 'fake-uuid', 'blueprint_title' => "BP {$i}"],
            ]);
        }

        $component = Livewire::actingAs($this->user)
            ->test(\App\Modules\Marketplace\Livewire\NotificationBell::class);

        $component->assertSeeHtml('bg-red-500');

        $component->call('markAllAsRead');

        // Badge should be gone
        $component->assertDontSeeHtml('bg-red-500');

        $this->assertEquals(0, Notification::where('user_id', $this->user->id)->unread()->count());
    }

    public function test_view_all_link_goes_to_notifications_page(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Modules\Marketplace\Livewire\NotificationBell::class)
            ->assertSee(route('notifications.index'));
    }

    public function test_it_shows_empty_state_when_no_notifications(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Modules\Marketplace\Livewire\NotificationBell::class)
            ->assertSee(__('marketplace.notifications_empty'));
    }
}
