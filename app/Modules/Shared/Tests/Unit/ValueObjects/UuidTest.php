<?php

declare(strict_types=1);

namespace App\Modules\Shared\Tests\Unit\ValueObjects;

use App\Modules\Shared\ValueObjects\Uuid;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UuidTest extends TestCase
{
    public function test_it_generates_valid_uuid(): void
    {
        $uuid = Uuid::generate();
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            (string) $uuid
        );
    }

    public function test_it_accepts_valid_uuid_string(): void
    {
        $validUuid = '550e8400-e29b-41d4-a716-446655440000';
        $uuid = new Uuid($validUuid);
        $this->assertEquals($validUuid, (string) $uuid);
    }

    public function test_it_throws_on_invalid_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Uuid('not-a-uuid');
    }

    public function test_equals_returns_true_for_same_uuid(): void
    {
        $uuid1 = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $uuid2 = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $this->assertTrue($uuid1->equals($uuid2));
    }

    public function test_equals_returns_false_for_different_uuid(): void
    {
        $uuid1 = Uuid::generate();
        $uuid2 = Uuid::generate();
        $this->assertFalse($uuid1->equals($uuid2));
    }
}
