<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

use App\Modules\Blueprint\Enums\TabType;

/**
 * Represents the output of a single tab after processing.
 */
final class TabOutput
{
    public function __construct(
        public readonly TabType $type,
        public readonly mixed $content,
        public readonly ?string $filename = null,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'content' => $this->content,
            'filename' => $this->filename,
        ];
    }

    public function isMarkdown(): bool
    {
        return $this->content instanceof string;
    }

    public function isArray(): bool
    {
        return is_array($this->content);
    }
}
