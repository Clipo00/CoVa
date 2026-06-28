<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class ResetPasswordFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_reset_password_with_valid_token_updates_password(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('old-password'),
        ]);

        $token = Password::createToken($user);

        Livewire::test('auth.forms.reset-password-form', [
            'token' => $token,
            'email' => 'john@example.com',
        ])
            ->set('password', 'NewSecureP@ss1')
            ->set('password_confirmation', 'NewSecureP@ss1')
            ->call('resetPassword')
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertTrue(Hash::check('NewSecureP@ss1', $user->password));
    }

    public function test_reset_password_with_valid_token_logs_user_in(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('old-password'),
        ]);

        $token = Password::createToken($user);

        Livewire::test('auth.forms.reset-password-form', [
            'token' => $token,
            'email' => 'john@example.com',
        ])
            ->set('password', 'NewSecureP@ss1')
            ->set('password_confirmation', 'NewSecureP@ss1')
            ->call('resetPassword')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_reset_password_with_invalid_token_does_not_update_password(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('old-password'),
        ]);

        Livewire::test('auth.forms.reset-password-form', [
            'token' => 'invalid-token',
            'email' => 'john@example.com',
        ])
            ->set('password', 'NewSecureP@ss1')
            ->set('password_confirmation', 'NewSecureP@ss1')
            ->call('resetPassword')
            ->assertHasErrors(['email']);

        $user->refresh();
        $this->assertTrue(Hash::check('old-password', $user->password));
    }

    public function test_reset_password_with_short_password_shows_validation_error(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('old-password'),
        ]);

        $token = Password::createToken($user);

        Livewire::test('auth.forms.reset-password-form', [
            'token' => $token,
            'email' => 'john@example.com',
        ])
            ->set('password', 'short')
            ->set('password_confirmation', 'short')
            ->call('resetPassword')
            ->assertHasErrors(['password']);
    }

    public function test_reset_password_with_mismatched_confirmation_shows_validation_error(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('old-password'),
        ]);

        $token = Password::createToken($user);

        Livewire::test('auth.forms.reset-password-form', [
            'token' => $token,
            'email' => 'john@example.com',
        ])
            ->set('password', 'NewSecureP@ss1')
            ->set('password_confirmation', 'DifferentP@ss2')
            ->call('resetPassword')
            ->assertHasErrors(['password_confirmation']);
    }

    public function test_reset_password_without_mixed_types_shows_validation_error(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('old-password'),
        ]);

        $token = Password::createToken($user);

        Livewire::test('auth.forms.reset-password-form', [
            'token' => $token,
            'email' => 'john@example.com',
        ])
            ->set('password', 'onlylowercase')
            ->set('password_confirmation', 'onlylowercase')
            ->call('resetPassword')
            ->assertHasErrors(['password']);
    }

    public function test_reset_password_invalidates_other_devices_sessions(): void
    {
        Event::fake();

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('old-password'),
        ]);

        $token = Password::createToken($user);
        $newPassword = 'NewSecureP@ss1';

        Livewire::test('auth.forms.reset-password-form', [
            'token' => $token,
            'email' => 'john@example.com',
        ])
            ->set('password', $newPassword)
            ->set('password_confirmation', $newPassword)
            ->call('resetPassword')
            ->assertRedirect(route('dashboard'));

        Event::assertDispatched(\Illuminate\Auth\Events\OtherDeviceLogout::class);
    }
}
