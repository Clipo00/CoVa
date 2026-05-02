<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\Models\Blueprint;

class UpdateBlueprint
{
    public function execute(
        Blueprint $blueprint,
        array $data
    ): Blueprint {
        $blueprint->update($data);
        return $blueprint->fresh();
    }
}
