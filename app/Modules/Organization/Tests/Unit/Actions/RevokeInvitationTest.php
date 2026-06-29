<?php

declare(strict_types=1);

namespace App\Modules\Organization\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Actions\InviteUser;
use App\Modules\Organization\Actions\RevokeInvitation;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevokeInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    public function test_it_revokes_pending_invitation(): void
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

        $this->assertTrue($invitation->isValid());

        $revokeInvitation = new RevokeInvitation();
        $revokeInvitation->execute($invitation);

        $this->assertFalse($invitation->fresh()->isValid());
        $this->assertNotNull($invitation->fresh()->used_at);
    }

    public function test_revoking_already_used_invitation_is_noop(): void
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

        // Mark as already used
        $invitation->update(['used_at' => now()->subHour()]);
        $originalUsedAt = $invitation->fresh()->used_at;

        $revokeInvitation = new RevokeInvitation();
        $revokeInvitation->execute($invitation);

        // used_at should be updated to now, not the original time
        $this->assertNotEquals($originalUsedAt, $invitation->fresh()->used_at);
        $this->assertFalse($invitation->fresh()->isValid());
    }

    public function test_revoking_expired_invitation_still_marks_used(): void
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

        $revokeInvitation = new RevokeInvitation();
        $revokeInvitation->execute($invitation);

        $this->assertNotNull($invitation->fresh()->used_at);
    }
}
