<?php

declare(strict_types=1);

namespace App\Modules\Shared\Tests\Unit\ValueObjects;

use App\Modules\Shared\ValueObjects\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function test_it_creates_valid_email(): void
    {
        $email = new Email('test@example.com');
        $this->assertEquals('test@example.com', (string) $email);
    }

    public function test_it_lowercases_email(): void
    {
        $email = new Email('Test@Example.COM');
        $this->assertEquals('test@example.com', (string) $email);
    }

    public function test_it_throws_on_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Email('not-an-email');
    }

    public function test_it_throws_on_empty_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Email('');
    }

    public function test_equals_returns_true_for_same_email(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');
        $this->assertTrue($email1->equals($email2));
    }

    public function test_equals_returns_false_for_different_email(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('other@example.com');
        $this->assertFalse($email1->equals($email2));
    }
}
