<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthenticationRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

final class AuthenticationController extends Controller
{
    public function __invoke(AuthenticationRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::query()
            ->where('email', '=', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], status: 401);
        }

        // Issue token with abilities. Give super-admin tokens the 'web-login' ability
        // so they can be exchanged for a session on the Laravel backend.
        $abilities = $user->hasRole('super-admin') ? ['web-login'] : ['api'];
        $token = $user->createToken('auth_token', $abilities)->plainTextToken;

        // Get user roles and permissions
        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');

        // Determine redirect URL based on role
        $redirectUrl = $this->getRedirectUrl($user);
        // Make absolute URL to avoid front confusion
        if ($redirectUrl) {
            $redirectUrl = config('app.url').$redirectUrl;
        }

        // Explicit flag for frontend: is super-admin?
        $isSuperAdmin = $user->hasRole('super-admin');
        $webLoginUrl = $isSuperAdmin ? config('app.url').'/auth/web-login' : null;

        return response()->json([
            'message' => 'Authentication successful.',
            'token' => $token,
            'user' => $user->load('roles', 'permissions'),
            'roles' => $roles,
            'permissions' => $permissions,
            'redirect_url' => $redirectUrl,
            'is_super_admin' => $isSuperAdmin,
            'web_login_url' => $webLoginUrl,
        ], 200);
    }

    public function logout(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ], 200);
    }

    /**
     * Determine redirect URL based on user role
     * Only super-admin gets specific redirect to Laravel backend
     * Other roles will be handled by front-end Vue.js routing
     */
    private function getRedirectUrl(User $user): ?string
    {
        // Only super-admin has access to Laravel backend dashboard
        if ($user->hasRole('super-admin')) {
            return '/admin/dashboard'; // Laravel backend pages
        }

        // For other roles (admin, teacher, student, parent), return null
        // Front-end Vue.js will handle their routing based on permissions/roles
        return null;
    }
}
