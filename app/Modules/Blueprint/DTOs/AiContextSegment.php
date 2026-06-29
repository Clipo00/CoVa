<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

/**
 * Immutable value object representing a single segment in the AI Context configuration.
 *
 * Each segment has a type (preset, skill, or custom), a unique name within the
 * segment list, and optional content. When content is null, the system resolves
 * the default content from the SegmentRegistry at generation time.
 */
readonly class AiContextSegment
{
    public function __construct(
        public string $type,
        public string $name,
        public ?string $content = null,
    ) {
        if (!in_array($type, ['preset', 'skill', 'custom'], true)) {
            throw new \InvalidArgumentException("Invalid segment type: {$type}");
        }

        if ($name === '') {
            throw new \InvalidArgumentException('Segment name cannot be empty.');
        }
    }

    /**
     * Create a segment from an array (deserialization).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? throw new \InvalidArgumentException('Missing segment type.'),
            name: $data['name'] ?? throw new \InvalidArgumentException('Missing segment name.'),
            content: $data['content'] ?? null,
        );
    }

    /**
     * Serialize segment to array.
     *
     * @return array{type: string, name: string, content?: string}
     */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'name' => $this->name,
            'content' => $this->content,
        ], fn ($value) => $value !== null);
    }

    /**
     * Whether this is a preset segment sourced from the presets registry.
     */
    public function isPreset(): bool
    {
        return $this->type === 'preset';
    }

    /**
     * Whether this is a skill segment sourced from the skills registry.
     */
    public function isSkill(): bool
    {
        return $this->type === 'skill';
    }

    /**
     * Whether this is a user-defined custom segment.
     */
    public function isCustom(): bool
    {
        return $this->type === 'custom';
    }
}
