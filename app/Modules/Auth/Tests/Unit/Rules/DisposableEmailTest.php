<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit\Rules;

use App\Rules\DisposableEmail;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DisposableEmailTest extends TestCase
{
    private DisposableEmail $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new DisposableEmail();
    }

    public function test_it_rejects_disposable_email_domain(): void
    {
        $validator = Validator::make(
            ['email' => 'user@tempmail.com'],
            ['email' => [$this->rule]],
        );

        $this->assertTrue($validator->fails());
        $this->assertEquals(
            __('auth.disposable_email'),
            $validator->errors()->first('email'),
        );
    }

    public function test_it_accepts_legitimate_email_domain(): void
    {
        $validator = Validator::make(
            ['email' => 'user@gmail.com'],
            ['email' => [$this->rule]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_it_accepts_email_from_unknown_domain(): void
    {
        $validator = Validator::make(
            ['email' => 'user@customdomain.example'],
            ['email' => [$this->rule]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_it_rejects_email_from_another_known_disposable_domain(): void
    {
        $validator = Validator::make(
            ['email' => 'user@mailinator.com'],
            ['email' => [$this->rule]],
        );

        $this->assertTrue($validator->fails());
        $this->assertEquals(
            __('auth.disposable_email'),
            $validator->errors()->first('email'),
        );
    }

    public function test_it_handles_email_without_at_sign_gracefully(): void
    {
        $validator = Validator::make(
            ['email' => 'not-an-email'],
            ['email' => [$this->rule]],
        );

        // Without @, strrpos returns false; the rule should pass since no match
        $this->assertTrue($validator->passes());
    }
}
