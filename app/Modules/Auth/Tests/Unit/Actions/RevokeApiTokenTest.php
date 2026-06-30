<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit\Actions;

use App\Modules\Auth\Actions\RevokeApiToken;
use App\Modules\Auth\Models\User;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RevokeApiTokenTest extends TestCase
{
    use RefreshDatabase;

    private RevokeApiToken $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->action = new RevokeApiToken;
    }

    public function test_it_revokes_token_successfully(): void
    {
        $plan = Plan::where('slug', 'pro')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'plan_id' => $plan->id,
        ]);

        $token = $user->createToken('Test Token', ['*'], now()->addMonths(6));

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->accessToken->id,
            'name' => 'Test Token',
        ]);

        $this->action->execute(
            user: $user,
            tokenId: $token->accessToken->id,
            password: 'password123',
        );

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
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

        $token = $user->createToken('Test Token', ['*'], now()->addMonths(6));

        $this->expectException(ValidationException::class);

        $this->action->execute(
            user: $user,
            tokenId: $token->accessToken->id,
            password: 'wrong-password',
        );

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_it_throws_not_found_for_another_users_token(): void
    {
        $plan = Plan::where('slug', 'pro')->first();

        $owner = User::create([
            'name' => 'Token Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password123'),
            'plan_id' => $plan->id,
        ]);

        $attacker = User::create([
            'name' => 'Attacker',
            'email' => 'attacker@example.com',
            'password' => Hash::make('password123'),
            'plan_id' => $plan->id,
        ]);

        $token = $owner->createToken('Owner Token', ['*'], now()->addMonths(6));

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->action->execute(
            user: $attacker,
            tokenId: $token->accessToken->id,
            password: 'password123',
        );

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }
}
