<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class AuthuserinfoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        return response()->json([
            'message' => 'Informations utilisateur récupérées',
            'user' => $user,
            'roles' => $user ? $user->getRoleNames() : [],
            'permissions' => $user ? $user->getAllPermissions()->pluck('name') : [],
            'token' => $request->bearerToken(),
        ], 200);
    }
}
