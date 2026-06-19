<?php

declare(strict_types=1);

namespace App\Modules\Organization\Exceptions;

use Exception;

class MaxMembersReachedException extends Exception
{
    public function __construct(int $limit, string $planName)
    {
        parent::__construct(
            __('organization.max_members_reached', ['limit' => $limit, 'plan' => $planName])
        );
    }
}
