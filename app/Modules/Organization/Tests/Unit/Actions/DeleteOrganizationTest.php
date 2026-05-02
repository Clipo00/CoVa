<?php

declare(strict_types=1);

namespace App\Modules\Organization\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\DeleteOrganization;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteOrganizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    public function test_it_soft_deletes_organization(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createAction = new CreateOrganization();
        $organization = $createAction->execute($user, 'My Org', 'my-org');

        $this->assertDatabaseHas('organizations', ['slug' => 'my-org']);

        $deleteAction = new DeleteOrganization();
        $deleteAction->execute($organization);

        $this->assertSoftDeleted('organizations', ['slug' => 'my-org']);
    }
}
