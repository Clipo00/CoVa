<?php

declare(strict_types=1);

namespace App\Modules\Shared\Tests\Unit\Services;

use App\Modules\Shared\Services\JsonValidator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class JsonValidatorTest extends TestCase
{
    public function test_it_validates_valid_json(): void
    {
        $validator = new JsonValidator();
        $this->assertTrue($validator->isValid('{"key": "value"}'));
    }

    public function test_it_rejects_invalid_json(): void
    {
        $validator = new JsonValidator();
        $this->assertFalse($validator->isValid('{"key": invalid}'));
    }

    public function test_validate_throws_on_invalid_json(): void
    {
        $validator = new JsonValidator();
        $this->expectException(InvalidArgumentException::class);
        $validator->validate('invalid json');
    }

    public function test_decode_returns_array(): void
    {
        $validator = new JsonValidator();
        $result = $validator->decode('{"key": "value"}');
        $this->assertEquals(['key' => 'value'], $result);
    }

    public function test_decode_throws_on_non_array(): void
    {
        $validator = new JsonValidator();
        $this->expectException(InvalidArgumentException::class);
        $validator->decode('"just a string"');
    }

    public function test_encode_returns_json_string(): void
    {
        $validator = new JsonValidator();
        $json = $validator->encode(['key' => 'value']);
        $this->assertEquals('{"key":"value"}', $json);
    }
}
