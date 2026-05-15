<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

/**
 * Configuration for VSCode Extensions tab.
 *
 * @param string[] $extensions List of VSCode extension IDs
 */
final class VscodeExtensionsConfig
{
    /**
     * @param string[] $extensions
     */
    public function __construct(
        public readonly array $extensions = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            extensions: self::filterExtensions($data['extensions'] ?? []),
        );
    }

    /**
     * @param mixed[] $extensions
     * @return string[]
     */
    private static function filterExtensions(array $extensions): array
    {
        return array_values(array_filter(
            array_map(fn($ext) => is_string($ext) ? trim($ext) : null, $extensions),
            fn($ext) => $ext !== null && $ext !== ''
        ));
    }

    public function hasExtensions(): bool
    {
        return count($this->extensions) > 0;
    }

    public function installCommand(): string
    {
        if (!$this->hasExtensions()) {
            return '';
        }

        return 'code --install-extension ' . implode(' --install-extension ', $this->extensions);
    }
}
