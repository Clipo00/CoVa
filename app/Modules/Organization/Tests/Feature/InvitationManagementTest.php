<?php

declare(strict_types=1);

namespace App\Modules\Organization\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Actions\InviteUser;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InvitationManagementTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $owner;

    private User $maintainer;

    private User $developer;

    private User $nonMember;

    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);

        $this->plan = Plan::where('slug', 'free')->first();

        $this->owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);

        $this->maintainer = User::create([
            'name' => 'Maintainer',
            'email' => 'maintainer@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);

        $this->developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);

        $this->nonMember = User::create([
            'name' => 'NonMember',
            'email' => 'nonmember@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);

        $createOrg = new CreateOrganization;
        $this->organization = $createOrg->execute($this->owner, 'Test Org', 'test-org');

        // Add maintainer and developer as members
        $this->organization->members()->attach($this->maintainer->id, ['role' => 'maintainer']);
        $this->organization->members()->attach($this->developer->id, ['role' => 'developer']);
    }

    private function createPendingInvitation(string $email = 'invited@example.com')
    {
        $inviteUser = new InviteUser;
        Notification::fake(); // Prevent actual notifications during setup

        return $inviteUser->execute($this->organization, $email, 'developer');
    }

    // ─── Revoke Tests ───────────────────────────────────────────────

    public function test_owner_can_revoke_pending_invitation(): void
    {
        $invitation = $this->createPendingInvitation();

        $response = $this->actingAs($this->owner)
            ->delete(route('organizations.invitations.revoke', [
                $this->organization->slug,
                $invitation->id,
            ]));

        $response->assertRedirect(route('organizations.members', $this->organization->slug));
        $response->assertSessionHas('success', __('organization.invitation_revoked'));

        $this->assertFalse($invitation->fresh()->isValid());
        $this->assertNotNull($invitation->fresh()->used_at);
    }

    public function test_maintainer_cannot_revoke_pending_invitation(): void
    {
        $invitation = $this->createPendingInvitation();

        $response = $this->actingAs($this->maintainer)
            ->delete(route('organizations.invitations.revoke', [
                $this->organization->slug,
                $invitation->id,
            ]));

        $response->assertForbidden();
        $this->assertTrue($invitation->fresh()->isValid());
    }

    public function test_developer_cannot_revoke_invitation(): void
    {
        $invitation = $this->createPendingInvitation();

        $response = $this->actingAs($this->developer)
            ->delete(route('organizations.invitations.revoke', [
                $this->organization->slug,
                $invitation->id,
            ]));

        $response->assertForbidden();
        $this->assertTrue($invitation->fresh()->isValid());
    }

    public function test_non_member_cannot_revoke_invitation(): void
    {
        $invitation = $this->createPendingInvitation();

        $response = $this->actingAs($this->nonMember)
            ->delete(route('organizations.invitations.revoke', [
                $this->organization->slug,
                $invitation->id,
            ]));

        $response->assertForbidden();
        $this->assertTrue($invitation->fresh()->isValid());
    }

    public function test_guest_cannot_revoke_invitation(): void
    {
        $invitation = $this->createPendingInvitation();

        $response = $this->delete(route('organizations.invitations.revoke', [
            $this->organization->slug,
            $invitation->id,
        ]));

        $response->assertRedirect(route('login'));
        $this->assertTrue($invitation->fresh()->isValid());
    }

    public function test_revoke_returns_404_for_invitation_in_other_org(): void
    {
        $invitation = $this->createPendingInvitation();

        // Create another org
        $otherOwner = User::create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);
        $createOrg = new CreateOrganization;
        $otherOrg = $createOrg->execute($otherOwner, 'Other Org', 'other-org');

        // Try to revoke using other org's slug
        $response = $this->actingAs($otherOwner)
            ->delete(route('organizations.invitations.revoke', [
                $otherOrg->slug,
                $invitation->id,
            ]));

        $response->assertNotFound();
    }

    // ─── Resend Tests ───────────────────────────────────────────────

    public function test_owner_can_resend_pending_invitation(): void
    {
        Notification::fake();

        $invitation = $this->createPendingInvitation();
        $originalExpiresAt = $invitation->fresh()->expires_at;

        $this->travel(1)->hour();

        Notification::fake(); // Reset fake to only count resend

        $response = $this->actingAs($this->owner)
            ->post(route('organizations.invitations.resend', [
                $this->organization->slug,
                $invitation->id,
            ]));

        $response->assertRedirect(route('organizations.members', $this->organization->slug));
        $response->assertSessionHas('success', __('organization.invitation_resent'));

        $refreshed = $invitation->fresh();
        $this->assertGreaterThan($originalExpiresAt, $refreshed->expires_at);
        $this->assertTrue($refreshed->isValid());

        Notification::assertCount(1);
    }

    public function test_maintainer_cannot_resend_pending_invitation(): void
    {
        Notification::fake();
        $invitation = $this->createPendingInvitation();

        $response = $this->actingAs($this->maintainer)
            ->post(route('organizations.invitations.resend', [
                $this->organization->slug,
                $invitation->id,
            ]));

        $response->assertForbidden();
    }

    public function test_developer_cannot_resend_invitation(): void
    {
        $invitation = $this->createPendingInvitation();
        Notification::fake();
        $originalExpiresAt = $invitation->fresh()->expires_at;

        $response = $this->actingAs($this->developer)
            ->post(route('organizations.invitations.resend', [
                $this->organization->slug,
                $invitation->id,
            ]));

        $response->assertForbidden();
        Notification::assertNothingSent();
        // Expiry should not have changed
        $this->assertEquals($originalExpiresAt->timestamp, $invitation->fresh()->expires_at->timestamp);
    }

    public function test_non_member_cannot_resend_invitation(): void
    {
        $invitation = $this->createPendingInvitation();
        Notification::fake();

        $response = $this->actingAs($this->nonMember)
            ->post(route('organizations.invitations.resend', [
                $this->organization->slug,
                $invitation->id,
            ]));

        $response->assertForbidden();
        Notification::assertNothingSent();
    }

    public function test_guest_cannot_resend_invitation(): void
    {
        $invitation = $this->createPendingInvitation();
        Notification::fake();

        $response = $this->post(route('organizations.invitations.resend', [
            $this->organization->slug,
            $invitation->id,
        ]));

        $response->assertRedirect(route('login'));
        Notification::assertNothingSent();
    }

    public function test_revoked_invitation_cannot_be_accepted(): void
    {
        $invitation = $this->createPendingInvitation('accept-test@example.com');

        // Revoke it
        $this->actingAs($this->owner)
            ->delete(route('organizations.invitations.revoke', [
                $this->organization->slug,
                $invitation->id,
            ]));

        // Try to accept it
        $user = User::create([
            'name' => 'Accept Test',
            'email' => 'accept-test@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('invitations.accept', $invitation->token));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
