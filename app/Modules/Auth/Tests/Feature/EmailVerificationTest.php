<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_valid_signed_url_verifies_email(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertNull($user->email_verified_at);

        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
        );

        $this->actingAs($user)
            ->get($signedUrl)
            ->assertRedirect(route('profile'));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_expired_signed_url_redirects_to_login(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinutes(5),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
        );

        $this->actingAs($user)
            ->get($expiredUrl)
            ->assertRedirect(route('login'));
    }

    public function test_tampered_hash_redirects_to_login(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
        );

        $tamperedUrl = str_replace(sha1($user->getEmailForVerification()), 'tamperedhash', $signedUrl);

        $this->actingAs($user)
            ->get($tamperedUrl)
            ->assertRedirect(route('login'));
    }

    public function test_already_verified_user_redirects_to_profile(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $hash = sha1($user->getEmailForVerification());

        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => $hash],
        );

        $this->actingAs($user)
            ->get($signedUrl)
            ->assertRedirect(route('profile'));
    }

    public function test_resend_verification_email_redirects_to_profile(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user)
            ->post(route('verification.resend'))
            ->assertRedirect(route('profile'));
    }
}
