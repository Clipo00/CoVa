<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Contracts;

use App\Modules\Blueprint\DTOs\TabOutput;

interface TabInterface
{
    /**
     * Returns the tab type identifier.
     */
    public function type(): string;

    /**
     * Generates tab output from configuration.
     *
     * @param array<string, mixed> $config
     */
    public function generate(array $config): TabOutput;
}
