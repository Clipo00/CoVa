<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit\Actions;

use App\Modules\Auth\Actions\CreateApiToken;
use App\Modules\Auth\Models\User;
use App\Modules\Shared\Models\Plan;
use Carbon\Carbon;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateApiTokenTest extends TestCase
{
    use RefreshDatabase;

    private CreateApiToken $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->action = new CreateApiToken;
    }

    public function test_it_creates_api_token_and_returns_plain_text(): void
    {
        $plan = Plan::where('slug', 'pro')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'plan_id' => $plan->id,
        ]);

        $token = $this->action->execute(
            user: $user,
            name: 'My API Token',
            expiresAt: Carbon::now()->addMonths(6),
            password: 'password123',
        );

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('|', $token);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'My API Token',
        ]);
    }

    public function test_it_throws_exception_when_expiration_exceeds_one_year(): void
    {
        $plan = Plan::where('slug', 'pro')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'plan_id' => $plan->id,
        ]);

        $this->expectException(ValidationException::class);

        $this->action->execute(
            user: $user,
            name: 'My API Token',
            expiresAt: Carbon::now()->addYears(2),
            password: 'password123',
        );
    }

    public function test_it_throws_exception_for_wrong_password(): void
    {
        $plan = Plan::where('slug', 'pro')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'plan_id' => $plan->id,
        ]);

        $this->expectException(ValidationException::class);

        $this->action->execute(
            user: $user,
            name: 'My API Token',
            expiresAt: Carbon::now()->addMonths(6),
            password: 'wrong-password',
        );
    }

    public function test_it_throws_exception_for_free_plan_user(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Free User',
            'email' => 'free@example.com',
            'password' => Hash::make('password123'),
            'plan_id' => $plan->id,
        ]);

        $this->expectException(\RuntimeException::class);

        $this->action->execute(
            user: $user,
            name: 'My API Token',
            expiresAt: Carbon::now()->addMonths(6),
            password: 'password123',
        );
    }
}
