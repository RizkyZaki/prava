<?php

namespace App\Http\Controllers\Api\V1\Account;

use App\Http\Controllers\Api\V1\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends BaseApiController
{
    /**
     * My account summary.
     * Endpoint: GET /api/v1/account
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->load('employeeProfile');

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'employee_profile' => $user->employeeProfile,
        ]);
    }

    /**
     * My account profile.
     * Endpoint: GET /api/v1/account/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load('employeeProfile');

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'fingerprint_id' => $user->fingerprint_id,
            'employee_profile' => $user->employeeProfile,
        ]);
    }

    /**
     * Update my account profile.
     * Endpoint: PUT /api/v1/account/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
        ]);

        $user = $request->user();

        if (array_key_exists('name', $validated) && $user) {
            $user->name = $validated['name'];
        }

        if (array_key_exists('email', $validated) && $user) {
            $user->email = $validated['email'];
        }

        $user?->save();
        $user?->refresh();

        return $this->success([
            'id' => $user?->id,
            'name' => $user?->name,
            'email' => $user?->email,
        ], 'Profile updated');
    }
}
