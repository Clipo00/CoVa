<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers\Api;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintVariable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AuthApiController
{
    /**
     * Return the authenticated user's profile and accessible organizations.
     *
     * Requires a valid Sanctum API token. Does NOT require the api.access
     * middleware — Free users can still validate their token via this endpoint.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $orgs = $user->organizations()->get(['organizations.name', 'organizations.slug']);

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'orgs' => $orgs,
        ]);
    }

    /**
     * Verify the user's password and return decrypted secret variables
     * for a blueprint. Password verification uses Hash::check() against
     * the authenticated user's hashed password.
     *
     * Rate limited to 5 attempts per minute (enforced by route middleware).
     */
    public function verifyPassword(Request $request, string $slug): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $blueprint = Blueprint::where('slug', $slug)->first();

        if (!$blueprint) {
            return response()->json([
                'type' => config('app.url') . '/errors/not-found',
                'title' => 'Not Found',
                'status' => 404,
                'detail' => 'Blueprint not found.',
            ], 404);
        }

        /** @var User $user */
        $user = $request->user();

        // Verify blueprint belongs to one of the user's organizations before decrypting
        $orgIds = $user->organizations()->pluck('organizations.id');

        if (!$blueprint->organization_id || !$orgIds->contains($blueprint->organization_id)) {
            return response()->json([
                'type' => config('app.url') . '/errors/not-found',
                'title' => 'Not Found',
                'status' => 404,
                'detail' => 'Blueprint not found.',
            ], 404);
        }

        // Account-level rate limiting: 10 attempts per user, then 15-minute lockout
        $throttleKey = 'verify-password:' . $user->id;

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'type' => config('app.url') . '/errors/forbidden',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => "Too many failed attempts. Try again in {$seconds} seconds.",
            ], 403);
        }

        if (!Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 900); // 15 minutes

            Log::warning('Failed password verification', [
                'user_id' => $user->id,
                'blueprint_slug' => $slug,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'type' => config('app.url') . '/errors/forbidden',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Password verification failed',
            ], 403);
        }

        // Clear rate limit on successful verification
        RateLimiter::clear($throttleKey);

        // Get secret variables and explicitly decrypt them
        $secrets = $blueprint->variables()
            ->where('is_secret', true)
            ->get()
            ->map(function (BlueprintVariable $variable) {
                $encrypted = $variable->getRawOriginal('default_value');

                if ($encrypted === null || $encrypted === '') {
                    return [
                        'key' => $variable->key,
                        'value' => '',
                    ];
                }

                try {
                    $decryptedValue = Crypt::decryptString($encrypted);
                } catch (\Throwable $e) {
                    Log::warning('Failed to decrypt secret variable', [
                        'key' => $variable->key,
                        'blueprint_id' => $blueprint->id,
                    ]);

                    $decryptedValue = '';
                }

                return [
                    'key' => $variable->key,
                    'value' => $decryptedValue,
                ];
            });

        return response()->json([
            'secrets' => $secrets,
        ]);
    }
}
