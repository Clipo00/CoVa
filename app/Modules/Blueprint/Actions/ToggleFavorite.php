<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;

class ToggleFavorite
{
    public function execute(Blueprint $blueprint, User $user): bool
    {
        $favorite = $blueprint->favorites()->where('user_id', $user->id)->first();

        if ($favorite) {
            $favorite->delete();
            return false;
        }

        $blueprint->favorites()->create(['user_id' => $user->id]);
        return true;
    }
}
