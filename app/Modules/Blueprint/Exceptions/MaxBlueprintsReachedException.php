<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Exceptions;

use Exception;

class MaxBlueprintsReachedException extends Exception
{
    public function __construct(int $maxBlueprints, string $planName)
    {
        parent::__construct(
            "Límite de {$maxBlueprints} blueprints por organización alcanzado en plan {$planName}."
        );
    }
}
