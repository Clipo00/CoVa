<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

/**
 * Configuration for AI Context tab.
 *
 * @param string[] $presets      List of preset names to include
 * @param string[] $skills       List of skill names to include
 * @param string   $customRules  Additional custom rules as markdown
 */
final class AiContextConfig
{
    /**
     * @param string[] $presets
     * @param string[] $skills
     */
    public function __construct(
        public readonly array $presets = [],
        public readonly array $skills = [],
        public readonly string $customRules = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            presets: self::filterStringArray($data['presets'] ?? []),
            skills: self::filterStringArray($data['skills'] ?? []),
            customRules: trim($data['custom_rules'] ?? ''),
        );
    }

    /**
     * @param mixed[] $array
     * @return string[]
     */
    private static function filterStringArray(array $array): array
    {
        return array_filter(
            array_map(fn($item) => is_string($item) ? trim($item) : null, $array),
            fn($item) => $item !== null && $item !== '',
        );
    }

    public function hasPresets(): bool
    {
        return count($this->presets) > 0;
    }

    public function hasSkills(): bool
    {
        return count($this->skills) > 0;
    }

    public function hasCustomRules(): bool
    {
        return $this->customRules !== '';
    }

    public function isEmpty(): bool
    {
        return !$this->hasPresets() && !$this->hasSkills() && !$this->hasCustomRules();
    }
}
