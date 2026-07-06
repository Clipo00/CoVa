<?php

declare(strict_types=1);

namespace App\Modules\Organization\Exceptions;

use Exception;

class MaxOrganizationsReachedException extends Exception
{
    public function __construct(int $maxOrganizations, string $planName)
    {
        parent::__construct(
            __('organization.max_reached', ['max' => $maxOrganizations, 'plan' => $planName])
        );
    }
}
