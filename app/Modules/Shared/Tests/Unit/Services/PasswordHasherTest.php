<?php

declare(strict_types=1);

namespace App\Modules\Shared\Tests\Unit\Services;

use App\Modules\Shared\Services\PasswordHasher;
use PHPUnit\Framework\TestCase;

class PasswordHasherTest extends TestCase
{
    public function test_it_hashes_password(): void
    {
        $hasher = new PasswordHasher();
        $hash = $hasher->hash('password123');

        $this->assertNotEquals('password123', $hash);
        $this->assertTrue(password_verify('password123', $hash));
    }

    public function test_it_verifies_correct_password(): void
    {
        $hasher = new PasswordHasher();
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $this->assertTrue($hasher->verify('password123', $hash));
    }

    public function test_it_rejects_incorrect_password(): void
    {
        $hasher = new PasswordHasher();
        $hash = password_hash('password123', PASSWORD_BCRYPT);

        $this->assertFalse($hasher->verify('wrongpassword', $hash));
    }
}
