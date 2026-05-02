<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\Models\Blueprint;

class DeleteBlueprint
{
    public function execute(Blueprint $blueprint): void
    {
        $blueprint->delete();
    }
}
