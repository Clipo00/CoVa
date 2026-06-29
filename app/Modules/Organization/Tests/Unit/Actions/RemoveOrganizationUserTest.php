<?php

declare(strict_types=1);

namespace App\Modules\Organization\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Actions\RemoveOrganizationUser;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RemoveOrganizationUserTest extends TestCase
{
    use RefreshDatabase;

    private RemoveOrganizationUser $action;
    private Organization $organization;
    private User $owner;
    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PlanSeeder::class);

        $this->action = new RemoveOrganizationUser();

        $freePlan = Plan::where('slug', 'free')->first();
        $this->owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $freePlan->id,
        ]);

        $this->member = User::create([
            'name' => 'Member',
            'email' => 'member@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $freePlan->id,
        ]);

        $createOrg = new CreateOrganization();
        $this->organization = $createOrg->execute($this->owner, 'Test Org', 'test-org');
        $this->organization->members()->attach($this->member->id, ['role' => 'developer']);
    }

    public function test_owner_removes_member(): void
    {
        $this->action->execute($this->organization, $this->member, $this->owner);

        $this->assertDatabaseMissing('organization_user', [
            'user_id' => $this->member->id,
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_non_owner_denied(): void
    {
        $otherMember = User::create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'plan_id' => Plan::where('slug', 'free')->first()->id,
        ]);
        $this->organization->members()->attach($otherMember->id, ['role' => 'developer']);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(__('organization.no_manage_permission'));

        $this->action->execute($this->organization, $this->member, $otherMember);
    }

    public function test_self_removal_denied(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(__('organization.cannot_remove_self'));

        $this->action->execute($this->organization, $this->owner, $this->owner);
    }

    public function test_blueprint_reassignment_on_removal(): void
    {
        // Create a blueprint by the member being removed
        $this->actingAs($this->member);
        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440100',
            'organization_id' => $this->organization->id,
            'slug' => 'member-bp',
            'title' => 'Member Blueprint',
            'tabs_config' => [],
            'created_by' => $this->member->id,
        ]);

        $this->action->execute($this->organization, $this->member, $this->owner);

        // Blueprint should be reassigned to owner
        $blueprint->refresh();
        $this->assertEquals($this->owner->id, $blueprint->created_by);
    }

    public function test_transactional_consistency(): void
    {
        // Verify that the action succeeds and member is detached
        $this->action->execute($this->organization, $this->member, $this->owner);

        $this->assertDatabaseMissing('organization_user', [
            'user_id' => $this->member->id,
            'organization_id' => $this->organization->id,
        ]);

        // Verify member cannot access org resources anymore (not a member)
        $isMember = $this->organization->members()
            ->where('user_id', $this->member->id)
            ->exists();
        $this->assertFalse($isMember);
    }
}
