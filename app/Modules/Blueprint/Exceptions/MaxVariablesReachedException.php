<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Exceptions;

use Exception;

class MaxVariablesReachedException extends Exception
{
    public function __construct(int $maxVariables, string $planName)
    {
        parent::__construct(
            "Límite de {$maxVariables} variables por blueprint alcanzado en plan {$planName}."
        );
    }
}
