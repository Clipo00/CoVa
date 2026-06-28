<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Models\OrganizationInvitation;

class RevokeInvitation
{
    public function execute(OrganizationInvitation $invitation): void
    {
        $invitation->update(['used_at' => now()]);
    }
}
