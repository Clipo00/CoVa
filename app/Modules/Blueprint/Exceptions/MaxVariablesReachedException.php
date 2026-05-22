<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Exceptions;

use Exception;

class MaxVariablesReachedException extends Exception
{
    public function __construct(int $maxVariables, string $planName)
    {
        parent::__construct(
            __('blueprint.max_variables_reached', ['max' => $maxVariables, 'plan' => $planName])
        );
    }
}
