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

        $orgs = $user->organizations()->get(['organizations.id', 'name', 'slug']);

        return response()->json([
            'user' => [
                'id' => $user->id,
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

        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'type' => config('app.url') . '/errors/forbidden',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Password verification failed',
            ], 403);
        }

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

                return [
                    'key' => $variable->key,
                    'value' => Crypt::decryptString($encrypted),
                ];
            });

        return response()->json([
            'secrets' => $secrets,
        ]);
    }
}
