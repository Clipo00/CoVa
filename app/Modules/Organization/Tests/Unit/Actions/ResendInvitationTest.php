<?php

declare(strict_types=1);

namespace App\Modules\Organization\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Actions\InviteUser;
use App\Modules\Organization\Actions\ResendInvitation;
use App\Modules\Organization\Notifications\OrganizationInvitationNotification;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResendInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_it_resends_pending_invitation_and_resets_expiry(): void
    {
        Notification::fake();

        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($user, 'My Org', 'my-org');

        $inviteUser = new InviteUser;
        $invitation = $inviteUser->execute($organization, 'invite@example.com', 'developer');

        $originalExpiresAt = $invitation->fresh()->expires_at;

        // Travel forward 1 hour to simulate time passing
        $this->travel(1)->hour();

        // Clear the initial invite notification from the fake
        Notification::fake();

        $resendInvitation = new ResendInvitation;
        $resendInvitation->execute($invitation);

        $refreshed = $invitation->fresh();

        // Expiry should be reset to ~48h from now (after travel)
        $this->assertGreaterThan($originalExpiresAt, $refreshed->expires_at);
        $this->assertTrue($refreshed->isValid());

        // Notification should have been sent once (the resend)
        Notification::assertCount(1);
    }

    public function test_it_sends_notification_when_resending(): void
    {
        Notification::fake();

        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($user, 'My Org', 'my-org');

        $inviteUser = new InviteUser;
        $invitation = $inviteUser->execute($organization, 'invite@example.com', 'developer');

        // Clear the fake to only count resend notification
        Notification::fake();

        $resendInvitation = new ResendInvitation;
        $resendInvitation->execute($invitation);

        Notification::assertSentOnDemand(
            OrganizationInvitationNotification::class,
            function ($notification, $channels, $notifiable) {
                return in_array('mail', $channels);
            }
        );
    }

    public function test_resending_updates_expiry_even_if_used(): void
    {
        Notification::fake();

        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($user, 'My Org', 'my-org');

        $inviteUser = new InviteUser;
        $invitation = $inviteUser->execute($organization, 'invite@example.com', 'developer');

        $originalExpiresAt = $invitation->fresh()->expires_at;

        // Mark as used (accepted)
        $invitation->update(['used_at' => now()]);

        $this->travel(1)->hour();

        $resendInvitation = new ResendInvitation;
        $resendInvitation->execute($invitation);

        // Expiry is still updated (Action doesn't check validity — controller does)
        $this->assertGreaterThan($originalExpiresAt, $invitation->fresh()->expires_at);
    }
}
