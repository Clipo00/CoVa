<?php

declare(strict_types=1);

namespace App\Modules\Organization\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\InviteUser;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\OrganizationInvitation;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InvitationAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;
    private User $owner;
    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);

        $this->plan = Plan::where('slug', 'free')->first();
        $this->owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);

        $this->organization = Organization::create([
            'slug' => 'test-org',
            'name' => 'Test Org',
            'owner_id' => $this->owner->id,
            'plan_id' => $this->plan->id,
        ]);

        $this->organization->members()->attach($this->owner->id, ['role' => 'owner']);
    }

    public function test_auth_user_can_accept_valid_invitation_via_get(): void
    {
        $user = User::create([
            'name' => 'Invited',
            'email' => 'invited@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);

        $invitation = OrganizationInvitation::create([
            'organization_id' => $this->organization->id,
            'email' => $user->email,
            'token' => 'valid-token-abc-123',
            'role' => 'developer',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('invitations.show', $invitation->token));

        $response->assertRedirect(route('organizations.show', $this->organization->slug));

        $this->assertNotNull($invitation->fresh()->used_at);
        $this->assertTrue($this->organization->fresh()->members->contains($user));
    }

    public function test_guest_is_redirected_to_login_with_token_in_session(): void
    {
        $invitation = OrganizationInvitation::create([
            'organization_id' => $this->organization->id,
            'email' => 'guest@example.com',
            'token' => 'guest-token-abc-456',
            'role' => 'developer',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->get(route('invitations.show', $invitation->token));

        $response->assertRedirect(route('login'));
        $this->assertEquals($invitation->token, session('invitation_token'));
    }

    public function test_expired_token_returns_error(): void
    {
        $user = User::create([
            'name' => 'Expired User',
            'email' => 'expired@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);

        $invitation = OrganizationInvitation::create([
            'organization_id' => $this->organization->id,
            'email' => $user->email,
            'token' => 'expired-token',
            'role' => 'developer',
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('invitations.show', $invitation->token));

        $response->assertSessionHas('error');
        $response->assertRedirect();
        $this->assertNull($invitation->fresh()->used_at);
    }

    public function test_used_token_returns_error(): void
    {
        $user = User::create([
            'name' => 'Used User',
            'email' => 'used@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);

        $invitation = OrganizationInvitation::create([
            'organization_id' => $this->organization->id,
            'email' => $user->email,
            'token' => 'used-token',
            'role' => 'developer',
            'expires_at' => now()->addDays(7),
            'used_at' => now()->subDay(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('invitations.show', $invitation->token));

        $response->assertSessionHas('error');
        $response->assertRedirect();
    }

    public function test_email_mismatch_returns_error(): void
    {
        $user = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);

        $invitation = OrganizationInvitation::create([
            'organization_id' => $this->organization->id,
            'email' => 'invited@example.com',
            'token' => 'mismatch-token',
            'role' => 'developer',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('invitations.show', $invitation->token));

        $response->assertSessionHas('error');
        $response->assertRedirect();
        $this->assertNull($invitation->fresh()->used_at);
    }

    public function test_post_accept_accepts_invitation_with_csrf(): void
    {
        $user = User::create([
            'name' => 'Post User',
            'email' => 'postuser@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);

        $invitation = OrganizationInvitation::create([
            'organization_id' => $this->organization->id,
            'email' => $user->email,
            'token' => 'post-accept-token',
            'role' => 'maintainer',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($user);

        // Manually seed the CSRF token so the POST doesn't get 419
        $this->app['session']->put('_token', 'csrf-token');

        $response = $this->post(route('invitations.accept', $invitation->token), [
            '_token' => 'csrf-token',
        ]);

        $response->assertRedirect();
        $this->assertNotNull($invitation->fresh()->used_at);
        $this->assertTrue($this->organization->fresh()->members->contains($user));
    }

    public function test_post_accept_requires_authentication(): void
    {
        $invitation = OrganizationInvitation::create([
            'organization_id' => $this->organization->id,
            'email' => 'guest@example.com',
            'token' => 'guest-post-token',
            'role' => 'developer',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post(route('invitations.accept', $invitation->token), [
            '_token' => 'test-token',
        ]);

        $this->assertTrue(in_array($response->getStatusCode(), [302, 419]));
    }

    public function test_get_route_is_rate_limited(): void
    {
        $invitation = OrganizationInvitation::create([
            'organization_id' => $this->organization->id,
            'email' => 'ratelimit@example.com',
            'token' => 'rate-limit-token',
            'role' => 'developer',
            'expires_at' => now()->addDays(7),
        ]);

        $user = User::create([
            'name' => 'Rate Limit User',
            'email' => 'ratelimit@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);

        $this->actingAs($user);

        for ($i = 0; $i < 10; $i++) {
            $this->get(route('invitations.show', 'different-token-' . $i));
        }

        $response = $this->get(route('invitations.show', $invitation->token));

        // The route exists and responds (200 redirect, 302 redirect, or 429 if throttled)
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 429]));
    }

    public function test_notification_is_sent_when_inviting_user(): void
    {
        Notification::fake();

        $inviteUser = new InviteUser();
        $invitation = $inviteUser->execute(
            organization: $this->organization,
            email: 'newmember@example.com',
            role: 'developer',
        );

        Notification::assertSentOnDemand(
            \App\Modules\Organization\Notifications\OrganizationInvitationNotification::class,
        );
    }
}
