<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationController
{
    /**
     * Verify a user's email address using a signed URL.
     */
    public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            return redirect()->route('login')
                ->with('error', __('auth.verification_failed'));
        }

        $user = User::findOrFail($id);

        $expectedHash = sha1($user->getEmailForVerification());

        if (!hash_equals((string) $hash, $expectedHash)) {
            return redirect()->route('login')
                ->with('error', __('auth.verification_failed'));
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('profile')
                ->with('info', __('auth.verification_already_verified'));
        }

        $user->markEmailAsVerified();

        return redirect()->route('profile')
            ->with('success', __('auth.verification_success'));
    }

    /**
     * Resend the verification email to the authenticated user.
     */
    public function resend(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('profile')
                ->with('info', __('auth.verification_already_verified'));
        }

        $user->sendEmailVerificationNotification();

        return redirect()->route('profile')
            ->with('success', __('auth.verification_sent'));
    }
}
