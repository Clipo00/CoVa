<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit\Actions;

use App\Modules\Auth\Actions\SendMfaCode;
use App\Modules\Auth\Actions\VerifyMfaCode;
use App\Modules\Auth\Models\MfaCode;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyMfaCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_validates_a_correct_unused_code(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $sendAction = new SendMfaCode;
        $sendAction->execute($user);

        $code = MfaCode::where('user_id', $user->id)->first();

        $verifyAction = new VerifyMfaCode;
        $result = $verifyAction->execute($user, $code->code);

        $this->assertTrue($result);
    }

    public function test_it_rejects_an_expired_code(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $mfaCode = MfaCode::create([
            'user_id' => $user->id,
            'code' => '123456',
            'expires_at' => now()->subMinutes(5),
        ]);

        $verifyAction = new VerifyMfaCode;
        $result = $verifyAction->execute($user, $mfaCode->code);

        $this->assertFalse($result);
    }

    public function test_it_rejects_a_used_code(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $mfaCode = MfaCode::create([
            'user_id' => $user->id,
            'code' => '123456',
            'expires_at' => now()->addMinutes(10),
            'used_at' => now(),
        ]);

        $verifyAction = new VerifyMfaCode;
        $result = $verifyAction->execute($user, $mfaCode->code);

        $this->assertFalse($result);
    }

    public function test_it_marks_code_as_used_after_verification(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $sendAction = new SendMfaCode;
        $sendAction->execute($user);

        $code = MfaCode::where('user_id', $user->id)->first();

        $verifyAction = new VerifyMfaCode;
        $verifyAction->execute($user, $code->code);

        $this->assertNotNull($code->fresh()->used_at);
    }

    public function test_it_rejects_wrong_code(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $sendAction = new SendMfaCode;
        $sendAction->execute($user);

        $verifyAction = new VerifyMfaCode;
        $result = $verifyAction->execute($user, '000000');

        $this->assertFalse($result);
    }
}
