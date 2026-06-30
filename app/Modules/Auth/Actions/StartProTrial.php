<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Shared\Models\Plan;
use RuntimeException;

final class StartProTrial
{
    public function execute(User $user): void
    {
        // Only Free plan users can start a trial
        $freePlan = Plan::where('slug', 'free')->first();
        if ($user->plan_id !== $freePlan?->id) {
            throw new RuntimeException('Solo los usuarios del plan Free pueden iniciar la prueba.');
        }

        // Only one trial ever
        if ($user->trial_used_at !== null) {
            throw new RuntimeException('Ya has usado tu periodo de prueba.');
        }

        // Trial still active
        if ($user->trial_ends_at !== null && $user->trial_ends_at->isFuture()) {
            throw new RuntimeException('Tu periodo de prueba aún está activo.');
        }

        $proPlan = Plan::where('slug', 'pro')->firstOrFail();

        $user->update([
            'plan_id' => $proPlan->id,
            'trial_ends_at' => now()->addDays(14),
            'trial_used_at' => now(),
        ]);
    }
}
