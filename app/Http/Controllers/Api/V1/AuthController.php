<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Token sign-in for API clients. Same rules as the panel's sign-in form:
 * admins only, banned accounts refused, one generic message for every failure.
 */
class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $data['email'])->first();

        // One message for unknown email, wrong password, non-admin and banned,
        // so the endpoint never confirms which accounts exist.
        if (! $user || ! Hash::check($data['password'], $user->password) || ! $user->canSignIn() || $user->banned) {
            throw ValidationException::withMessages(['email' => 'Those credentials do not match our records.']);
        }

        $token = $user->createToken(
            $data['device_name'] ?? 'api',
            ['*'],
            now()->addMinutes(config('sanctum.expiration')),
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            'user' => new UserResource($user),
        ]);
    }

    /** Revokes only the token used for this request; other devices stay signed in. */
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
