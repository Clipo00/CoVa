<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit\Actions;

use App\Modules\Auth\Actions\RegisterUser;
use App\Modules\Auth\DTOs\RegisterUserData;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_user(): void
    {
        $action = new RegisterUser();
        $data = new RegisterUserData(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123',
            passwordConfirmation: 'password123',
        );

        $user = $action->execute($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $this->assertTrue(password_verify('password123', $user->password));
    }

    public function test_it_hashes_password(): void
    {
        $action = new RegisterUser();
        $data = new RegisterUserData(
            name: 'Jane Doe',
            email: 'jane@example.com',
            password: 'securepass123',
            passwordConfirmation: 'securepass123',
        );

        $user = $action->execute($data);

        $this->assertNotEquals('securepass123', $user->password);
        $this->assertTrue(password_verify('securepass123', $user->password));
    }
}
