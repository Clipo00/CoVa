<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

/**
 * Configuration for AI Context tab.
 *
 * Stores a unified list of segments (presets, skills, custom) that define
 * the AI Context content. Segments are ordered and resolved at generation time.
 */
final class AiContextConfig
{
    /**
     * @param AiContextSegment[] $segments Ordered list of context segments
     */
    public function __construct(
        public readonly array $segments = [],
    ) {}

    /**
     * Create config from array payload.
     *
     * Accepts either the new format with a `segments` key, or the legacy
     * format with separate `presets`, `skills`, and `custom_rules` keys.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // New format: segments array directly
        if (isset($data['segments']) && is_array($data['segments'])) {
            return new self(
                segments: array_map(
                    fn (array $segment) => AiContextSegment::fromArray($segment),
                    $data['segments'],
                ),
            );
        }

        // Legacy format: convert from presets, skills, custom_rules
        $segments = [];

        foreach (self::filterStringArray($data['presets'] ?? []) as $preset) {
            $segments[] = new AiContextSegment(type: 'preset', name: $preset);
        }

        foreach (self::filterStringArray($data['skills'] ?? []) as $skill) {
            $segments[] = new AiContextSegment(type: 'skill', name: $skill);
        }

        $customRules = self::resolveString($data, 'custom_rules') ?? self::resolveString($data, 'customRules');
        if ($customRules !== '') {
            $segments[] = new AiContextSegment(type: 'custom', name: 'custom', content: $customRules);
        }

        return new self(segments: $segments);
    }

    /**
     * Serialize config to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'segments' => array_map(fn (AiContextSegment $s) => $s->toArray(), $this->segments),
        ];
    }

    /**
     * Whether the config has any segments.
     */
    public function hasSegments(): bool
    {
        return count($this->segments) > 0;
    }

    /**
     * Whether the config is empty (no segments).
     */
    public function isEmpty(): bool
    {
        return empty($this->segments);
    }

    /**
     * Get segments filtered by type.
     *
     * @return AiContextSegment[]
     */
    public function segmentsByType(string $type): array
    {
        return array_values(
            array_filter($this->segments, fn (AiContextSegment $s) => $s->type === $type),
        );
    }

    /**
     * @param array<string, mixed> $array
     * @return string[]
     */
    private static function filterStringArray(array $array): array
    {
        return array_values(array_filter(
            array_map(fn ($item) => is_string($item) ? trim($item) : null, $array),
            fn ($item) => $item !== null && $item !== '',
        ));
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function resolveString(array $data, string $key): string
    {
        return trim((string) ($data[$key] ?? ''));
    }
}
