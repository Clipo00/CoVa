<?php

declare(strict_types=1);

namespace App\Modules\Shared\Tests\Unit\Services;

use App\Modules\Shared\Services\UuidGenerator;
use App\Modules\Shared\ValueObjects\Uuid;
use PHPUnit\Framework\TestCase;

class UuidGeneratorTest extends TestCase
{
    public function test_it_generates_uuid(): void
    {
        $generator = new UuidGenerator();
        $uuid = $generator->generate();

        $this->assertInstanceOf(Uuid::class, $uuid);
        $this->assertNotEmpty((string) $uuid);
    }

    public function test_it_creates_uuid_from_string(): void
    {
        $generator = new UuidGenerator();
        $uuid = $generator->fromString('550e8400-e29b-41d4-a716-446655440000');

        $this->assertInstanceOf(Uuid::class, $uuid);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', (string) $uuid);
    }
}
