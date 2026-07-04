<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Notifications\ProTrialStarted;
use App\Modules\Shared\Models\Plan;
use RuntimeException;

final class StartProTrial
{
    public function execute(User $user): void
    {
        // Require verified email for trial
        if (!$user->hasVerifiedEmail()) {
            throw new RuntimeException(__('landing.trial_email_required'));
        }

        // Only Free plan users can start a trial
        $freePlan = Plan::where('slug', 'free')->first();
        if ($user->plan_id !== $freePlan?->id) {
            throw new RuntimeException(__('landing.trial_free_only'));
        }

        // Only one trial ever
        if ($user->trial_used_at !== null) {
            throw new RuntimeException(__('landing.trial_already_used'));
        }

        // Trial still active
        if ($user->trial_ends_at !== null && $user->trial_ends_at->isFuture()) {
            throw new RuntimeException(__('landing.trial_still_active'));
        }

        $proPlan = Plan::where('slug', 'pro')->firstOrFail();

        $user->update([
            'plan_id' => $proPlan->id,
            'trial_ends_at' => now()->addDays(14),
            'trial_used_at' => now(),
        ]);

        // Send welcome email
        $user->notify(new ProTrialStarted(
            trialEndsAt: $user->trial_ends_at,
        ));
    }
}
