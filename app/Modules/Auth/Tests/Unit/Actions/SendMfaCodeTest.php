<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit\Actions;

use App\Modules\Auth\Actions\SendMfaCode;
use App\Modules\Auth\Models\MfaCode;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Notifications\MfaCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendMfaCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_six_digit_code_and_stores_in_database(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $action = new SendMfaCode;
        $action->execute($user);

        $this->assertDatabaseHas('mfa_codes', [
            'user_id' => $user->id,
            'used_at' => null,
        ]);

        $code = MfaCode::where('user_id', $user->id)->first();
        $this->assertNotNull($code);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code->code);
        $this->assertTrue($code->expires_at->isFuture());
    }

    public function test_it_sends_mfa_code_notification(): void
    {
        Notification::fake();

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $action = new SendMfaCode;
        $action->execute($user);

        Notification::assertSentTo(
            [$user],
            MfaCodeNotification::class,
        );
    }

    public function test_code_expiration_is_set_to_future(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $before = now();
        $action = new SendMfaCode;
        $action->execute($user);
        $after = now();

        $code = MfaCode::where('user_id', $user->id)->first();

        $this->assertNotNull($code);
        $this->assertTrue($code->expires_at->isFuture());
        // Verify it's approximately 10 minutes (between 9:55 and 10:05)
        $this->assertTrue($code->expires_at->between(
            $before->copy()->addMinutes(9)->addSeconds(55),
            $after->copy()->addMinutes(10)->addSeconds(5),
        ));
    }
}
