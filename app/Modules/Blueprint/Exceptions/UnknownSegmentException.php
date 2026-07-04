<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Exceptions;

class UnknownSegmentException extends \InvalidArgumentException
{
    public function __construct(string $name, string $registryType = 'segment')
    {
        parent::__construct("Unknown {$registryType}: '{$name}'.");
    }
}
