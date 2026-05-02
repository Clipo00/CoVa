<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\Models\Blueprint;

class RestoreBlueprint
{
    public function execute(Blueprint $blueprint): void
    {
        $blueprint->restore();
    }
}
