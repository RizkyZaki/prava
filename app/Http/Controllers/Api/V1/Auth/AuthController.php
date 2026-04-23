<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
    /**
    * Log in and issue an access token.
     * Endpoint: POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->string('email'))->first();

        if (!$user || !Hash::check($request->string('password')->toString(), $user->password)) {
            return $this->error('Invalid email or password', 401);
        }

        $expiryDays = (int) config('api.token_expiry_days', 30);
        $expiresAt = now()->addDays($expiryDays);
        $token = $user->createToken(
            name: 'api-token-' . now()->timestamp,
            expiresAt: $expiresAt
        );

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ?? null,
            ],
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toISOString(),
          ], 'Login successful');
    }

    /**
      * Log out the current authenticated user.
     * Endpoint: POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

          return $this->success(message: 'Logout successful');
    }

    /**
      * Return the current user profile from active token.
     * Endpoint: GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'id' => $user?->id,
            'name' => $user?->name,
            'email' => $user?->email,
        ]);
    }

    /**
        * Refresh token (revoke the current token and issue a new one).
     * Endpoint: POST /api/v1/auth/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user?->currentAccessToken()?->delete();

        $expiryDays = (int) config('api.token_expiry_days', 30);
        $expiresAt = now()->addDays($expiryDays);
        $token = $user->createToken(
            name: 'api-token-' . now()->timestamp,
            expiresAt: $expiresAt
        );

        return $this->success([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toISOString(),
        ], 'Token refreshed');
    }
}
