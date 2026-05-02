<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Models\Organization;

class DeleteOrganization
{
    public function execute(Organization $organization): void
    {
        $organization->delete();
    }
}
