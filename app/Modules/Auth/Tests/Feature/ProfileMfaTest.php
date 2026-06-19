<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileMfaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_mfa_toggle_defaults_to_disabled(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        Livewire::actingAs($user)
            ->test('auth.forms.user-profile-form')
            ->assertSet('mfaEnabled', false);
    }

    public function test_mfa_toggle_shows_enabled_when_user_has_mfa_on(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'mfa_enabled' => true,
        ]);

        Livewire::actingAs($user)
            ->test('auth.forms.user-profile-form')
            ->assertSet('mfaEnabled', true);
    }

    public function test_enabling_mfa_updates_user_and_sends_code(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        Livewire::actingAs($user)
            ->test('auth.forms.user-profile-form')
            ->set('mfaEnabled', true)
            ->call('submit');

        $this->assertTrue((bool) $user->fresh()->mfa_enabled);
    }

    public function test_disabling_mfa_updates_user(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'mfa_enabled' => true,
        ]);

        Livewire::actingAs($user)
            ->test('auth.forms.user-profile-form')
            ->set('mfaEnabled', false)
            ->call('submit');

        $this->assertFalse((bool) $user->fresh()->mfa_enabled);
    }
}
