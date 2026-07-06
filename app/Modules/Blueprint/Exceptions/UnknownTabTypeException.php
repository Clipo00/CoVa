<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Exceptions;

use App\Modules\Blueprint\Enums\TabType;
use InvalidArgumentException;

class UnknownTabTypeException extends InvalidArgumentException
{
    public function __construct(string $type, ?TabType $expected = null)
    {
        $message = "Unknown tab type: '{$type}'.";

        if ($expected !== null) {
            $message .= ' Expected one of: '.implode(', ', TabType::values()).'.';
        }

        parent::__construct($message);
    }
}
