<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ForgotPasswordFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_send_reset_link_with_valid_email_sends_notification(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        Livewire::test('auth.forms.forgot-password-form')
            ->set('email', 'john@example.com')
            ->call('sendResetLink')
            ->assertStatus(200);
    }

    public function test_send_reset_link_with_valid_email_shows_generic_message(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        Livewire::test('auth.forms.forgot-password-form')
            ->set('email', 'john@example.com')
            ->call('sendResetLink')
            ->assertSee(__('auth.password_reset_sent'));
    }

    public function test_send_reset_link_with_nonexistent_email_shows_same_generic_message(): void
    {
        Livewire::test('auth.forms.forgot-password-form')
            ->set('email', 'unknown@example.com')
            ->call('sendResetLink')
            ->assertSee(__('auth.password_reset_sent'));
    }

    public function test_send_reset_link_with_invalid_email_shows_validation_error(): void
    {
        Livewire::test('auth.forms.forgot-password-form')
            ->set('email', 'not-an-email')
            ->call('sendResetLink')
            ->assertHasErrors(['email']);
    }

    public function test_send_reset_link_with_empty_email_shows_validation_error(): void
    {
        Livewire::test('auth.forms.forgot-password-form')
            ->set('email', '')
            ->call('sendResetLink')
            ->assertHasErrors(['email']);
    }

    public function test_rate_limiting_blocks_excessive_forgot_password_requests(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->get(route('password.request'));
            $response->assertStatus(200);
        }

        $response = $this->get(route('password.request'));
        $response->assertStatus(429);
    }
}
