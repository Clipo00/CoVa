<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

/**
 * Immutable value object representing a single segment in the AI Context configuration.
 *
 * Each segment has a type (skill, custom, or agent), a unique name within the
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
        if (!in_array($type, ['skill', 'custom', 'agent'], true)) {
            throw new \InvalidArgumentException(__('blueprint.segment_type_invalid', ['type' => $type]));
        }

        if ($name === '') {
            throw new \InvalidArgumentException(__('blueprint.segment_name_empty'));
        }

        if ($type === 'custom' && $content === null) {
            throw new \InvalidArgumentException(__('blueprint.segment_custom_needs_content'));
        }
    }

    /**
     * Create a segment from an array (deserialization).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? throw new \InvalidArgumentException(__('blueprint.segment_type_missing')),
            name: $data['name'] ?? throw new \InvalidArgumentException(__('blueprint.segment_name_missing')),
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

    /**
     * Whether this is an agent segment that composes skills.
     */
    public function isAgent(): bool
    {
        return $this->type === 'agent';
    }
}
