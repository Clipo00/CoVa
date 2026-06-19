<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit\Actions;

use App\Modules\Auth\Actions\LoginUser;
use App\Modules\Auth\Actions\SendMfaCode;
use App\Modules\Auth\DTOs\LoginUserData;
use App\Modules\Auth\Exceptions\MfaRequiredException;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_in_user_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $action = new LoginUser();
        $data = new LoginUserData(
            email: 'john@example.com',
            password: 'password123',
        );

        $result = $action->execute($data);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_it_throws_exception_with_invalid_credentials(): void
    {
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $action = new LoginUser();
        $data = new LoginUserData(
            email: 'john@example.com',
            password: 'wrongpassword',
        );

        $this->expectException(ValidationException::class);

        $action->execute($data);
    }

    public function test_it_throws_mfa_required_exception_when_mfa_enabled(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'mfa_enabled' => true,
        ]);

        $action = new LoginUser(new SendMfaCode());
        $data = new LoginUserData(
            email: 'john@example.com',
            password: 'password123',
        );

        $this->expectException(MfaRequiredException::class);

        try {
            $action->execute($data);
        } catch (MfaRequiredException $e) {
            $this->assertEquals($user->id, $e->user->id);
            $this->assertFalse(auth()->check());
            throw $e;
        }
    }

    public function test_it_logs_in_normally_when_mfa_disabled(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'mfa_enabled' => false,
        ]);

        $action = new LoginUser(new SendMfaCode());
        $data = new LoginUserData(
            email: 'john@example.com',
            password: 'password123',
        );

        $result = $action->execute($data);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
        $this->assertAuthenticatedAs($user);
    }
}
