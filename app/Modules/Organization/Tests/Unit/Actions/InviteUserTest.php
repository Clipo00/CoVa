<?php

declare(strict_types=1);

namespace App\Modules\Organization\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\AcceptInvitation;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Actions\InviteUser;
use App\Modules\Organization\Models\OrganizationInvitation;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
