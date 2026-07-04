<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

use App\Modules\Blueprint\Enums\TabType;
use InvalidArgumentException;

/**
 * Wrapper for a single tab configuration from tabs_config JSON.
 */
final class TabConfig
{
    public function __construct(
        public readonly TabType $type,
        public readonly array $config,
    ) {}

    public static function fromArray(array $data): self
    {
        $typeValue = $data['type'] ?? null;

        if ($typeValue === null) {
            throw new InvalidArgumentException(__('blueprint.tab_type_missing'));
        }

        if (!TabType::isValid($typeValue)) {
            throw new InvalidArgumentException(__('blueprint.tab_type_invalid', ['type' => $typeValue]));
        }

        return new self(
            type: TabType::from($typeValue),
            config: $data['config'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'config' => $this->config,
        ];
    }
}
