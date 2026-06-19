<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Modules\Auth\Actions\SendMfaCode;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MfaChallengeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_challenge_page_requires_mfa_session(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'mfa_enabled' => true,
        ]);

        $this->actingAs($user)
            ->get(route('mfa.challenge'))
            ->assertOk();
    }

    public function test_valid_mfa_code_allows_login(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'mfa_enabled' => true,
        ]);

        session()->put('mfa_user_id', $user->id);

        $sendMfaCode = new SendMfaCode();
        $mfaCode = $sendMfaCode->execute($user);

        Livewire::test('auth.forms.mfa-challenge-form')
            ->set('code', $mfaCode->code)
            ->call('submit')
            ->assertRedirect(route('dashboard'));
    }

    public function test_invalid_mfa_code_shows_error(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'mfa_enabled' => true,
        ]);

        session()->put('mfa_user_id', $user->id);

        $sendMfaCode = new SendMfaCode();
        $sendMfaCode->execute($user);

        Livewire::test('auth.forms.mfa-challenge-form')
            ->set('code', '000000')
            ->call('submit')
            ->assertHasErrors('code');
    }

    public function test_expired_mfa_code_shows_error(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'mfa_enabled' => true,
        ]);

        session()->put('mfa_user_id', $user->id);

        // Create an expired code directly
        \App\Modules\Auth\Models\MfaCode::create([
            'user_id' => $user->id,
            'code' => '999999',
            'expires_at' => now()->subMinutes(5),
        ]);

        Livewire::test('auth.forms.mfa-challenge-form')
            ->set('code', '999999')
            ->call('submit')
            ->assertHasErrors('code');
    }

    public function test_resend_code_sends_new_code(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'mfa_enabled' => true,
        ]);

        session()->put('mfa_user_id', $user->id);

        Livewire::test('auth.forms.mfa-challenge-form')
            ->call('resend')
            ->assertSet('code', '')
            ->assertHasNoErrors();
    }
}
