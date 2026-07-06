<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit\Actions;

use App\Modules\Auth\Actions\RegisterUser;
use App\Modules\Auth\DTOs\RegisterUserData;
use App\Modules\Auth\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_it_creates_a_user(): void
    {
        $action = new RegisterUser;
        $data = new RegisterUserData(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123',
        );

        $user = $action->execute($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotNull($user->plan_id);
    }

    public function test_it_hashes_password(): void
    {
        $action = new RegisterUser;
        $data = new RegisterUserData(
            name: 'Jane Doe',
            email: 'jane@example.com',
            password: 'securepass123',
        );

        $user = $action->execute($data);

        $this->assertNotEquals('securepass123', $user->password);
        $this->assertTrue(Hash::check('securepass123', $user->password));
    }

    public function test_it_assigns_free_plan_by_default(): void
    {
        $action = new RegisterUser;
        $data = new RegisterUserData(
            name: 'Free User',
            email: 'free@example.com',
            password: 'password123',
        );

        $user = $action->execute($data);

        $this->assertNotNull($user->plan);
        $this->assertEquals('free', $user->plan->slug);
    }

    public function test_it_rejects_disposable_email(): void
    {
        $this->expectException(ValidationException::class);

        $action = new RegisterUser;
        $data = new RegisterUserData(
            name: 'Temp User',
            email: 'user@mailinator.com',
            password: 'password123',
        );

        $action->execute($data);
    }

    public function test_it_sends_verification_notification_after_registration(): void
    {
        Notification::fake();

        $action = new RegisterUser;
        $data = new RegisterUserData(
            name: 'Jane Doe',
            email: 'jane@example.com',
            password: 'securepass123',
        );

        $user = $action->execute($data);

        Notification::assertSentTo(
            [$user],
            VerifyEmail::class,
        );
    }
}
