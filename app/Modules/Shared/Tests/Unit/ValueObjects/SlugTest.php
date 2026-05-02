<?php

declare(strict_types=1);

namespace App\Modules\Shared\Tests\Unit\ValueObjects;

use App\Modules\Shared\ValueObjects\Slug;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SlugTest extends TestCase
{
    public function test_it_creates_valid_slug(): void
    {
        $slug = new Slug('my-awesome-slug');
        $this->assertEquals('my-awesome-slug', (string) $slug);
    }

    public function test_it_lowercases_slug(): void
    {
        $slug = new Slug('My-Awesome-Slug');
        $this->assertEquals('my-awesome-slug', (string) $slug);
    }

    public function test_it_sanitizes_special_characters(): void
    {
        $slug = new Slug('My Awesome Slug!');
        $this->assertEquals('my-awesome-slug', (string) $slug);
    }

    public function test_it_sanitizes_underscores_to_hyphens(): void
    {
        $slug = new Slug('my_awesome_slug');
        $this->assertEquals('my-awesome-slug', (string) $slug);
    }

    public function test_it_throws_on_empty_after_sanitization(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Slug('!!!@#$%');
    }

    public function test_from_string_creates_slug(): void
    {
        $slug = Slug::fromString('hello-world');
        $this->assertEquals('hello-world', (string) $slug);
    }

    public function test_equals_returns_true_for_same_slug(): void
    {
        $slug1 = new Slug('test-slug');
        $slug2 = new Slug('test-slug');
        $this->assertTrue($slug1->equals($slug2));
    }
}
