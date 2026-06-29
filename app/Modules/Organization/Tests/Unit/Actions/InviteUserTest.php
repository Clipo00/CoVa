<?php

declare(strict_types=1);

namespace App\Modules\Organization\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\AcceptInvitation;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Actions\InviteUser;
use App\Modules\Organization\Exceptions\MaxMembersReachedException;
use App\Modules\Organization\Models\OrganizationInvitation;
use App\Modules\Organization\Notifications\OrganizationInvitationNotification;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InviteUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    public function test_it_creates_invitation(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($user, 'My Org', 'my-org');

        $inviteUser = new InviteUser();
        $invitation = $inviteUser->execute($organization, 'invite@example.com', 'developer');

        $this->assertInstanceOf(OrganizationInvitation::class, $invitation);
        $this->assertEquals('invite@example.com', $invitation->email);
        $this->assertEquals('developer', $invitation->role);
        $this->assertNotEmpty($invitation->token);
        $this->assertTrue($invitation->isValid());
    }

    public function test_accept_invitation_adds_user_to_organization(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $member = User::create([
            'name' => 'Member',
            'email' => 'member@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'My Org', 'my-org');

        $inviteUser = new InviteUser();
        $invitation = $inviteUser->execute($organization, 'member@example.com', 'maintainer');

        $acceptInvitation = new AcceptInvitation();
        $result = $acceptInvitation->execute($invitation->token, $member);

        $this->assertEquals($member->id, $result->id);
        $this->assertTrue($organization->fresh()->members->contains($member));
        $this->assertEquals('maintainer', $organization->fresh()->members->find($member->id)->pivot->role);
    }

    public function test_it_rejects_expired_invitation(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($user, 'My Org', 'my-org');

        $inviteUser = new InviteUser();
        $invitation = $inviteUser->execute($organization, 'invite@example.com', 'developer', -1);

        $this->assertFalse($invitation->fresh()->isValid());

        $this->expectException(ValidationException::class);
        $acceptInvitation = new AcceptInvitation();
        $acceptInvitation->execute($invitation->token);
    }

    public function test_accept_invitation_rejects_email_mismatch(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $otherUser = User::create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'My Org', 'my-org');

        $inviteUser = new InviteUser();
        $invitation = $inviteUser->execute($organization, 'invited@example.com', 'developer');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__('organization.invitation_email_mismatch'));

        $acceptInvitation = new AcceptInvitation();
        $acceptInvitation->execute($invitation->token, $otherUser);
    }

    public function test_accept_invitation_rejects_org_at_member_limit(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $member = User::create([
            'name' => 'Member',
            'email' => 'member@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'My Org', 'my-org');

        // Llenar la organización hasta el límite
        $maxMembers = $plan->max_members_per_org;
        if ($maxMembers !== null) {
            for ($i = 1; $i < $maxMembers; $i++) {
                $extraUser = User::create([
                    'name' => "Extra $i",
                    'email' => "extra$i@example.com",
                    'password' => bcrypt('password'),
                    'plan_id' => $plan->id,
                ]);
                $organization->members()->attach($extraUser->id, ['role' => 'developer']);
            }
        }

        $inviteUser = new InviteUser();
        $invitation = $inviteUser->execute($organization, 'member@example.com', 'developer');

        $this->expectException(MaxMembersReachedException::class);

        $acceptInvitation = new AcceptInvitation();
        $acceptInvitation->execute($invitation->token, $member);
    }

    public function test_it_rejects_invitation_for_existing_member(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $member = User::create([
            'name' => 'Existing Member',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'My Org', 'my-org');

        // Add member to organization first
        $organization->members()->attach($member->id, ['role' => 'developer']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__('organization.invite_already_member'));

        $inviteUser = new InviteUser();
        $inviteUser->execute($organization, 'existing@example.com', 'developer');
    }

    public function test_it_silently_skips_notification_for_existing_user_in_other_org(): void
    {
        Notification::fake();

        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        // This user exists in the system but is NOT in this organization
        $externalUser = User::create([
            'name' => 'External User',
            'email' => 'external@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'My Org', 'my-org');

        $inviteUser = new InviteUser();
        $invitation = $inviteUser->execute($organization, 'external@example.com', 'developer');

        // Invitation is created normally
        $this->assertInstanceOf(OrganizationInvitation::class, $invitation);
        $this->assertEquals('external@example.com', $invitation->email);

        // But NO notification is sent — prevents information disclosure
        // (the owner should not learn that this email belongs to a user in the system)
        Notification::assertNothingSent();
    }
}
