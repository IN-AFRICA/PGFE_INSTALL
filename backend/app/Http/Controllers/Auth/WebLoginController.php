<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as AuthFacade;
use Laravel\Sanctum\PersonalAccessToken;

final class WebLoginController extends Controller
{
    /**
     * Accept a bearer token or plain personal access token, find the token owner
     * and log them into the session, then redirect to the provided destination.
     */
    public function login(Request $request)
    {
        $tokenValue = $request->input('token') ?? $request->bearerToken();
        $redirect = $request->input('redirect') ?? '/';

        if (empty($tokenValue)) {
            return redirect('/login')->with('error', 'Token manquant');
        }

        $pat = PersonalAccessToken::findToken($tokenValue);
        if (! $pat) {
            return redirect('/login')->with('error', 'Token invalide');
        }

        // Ensure the personal access token has the 'web-login' ability
        $abilities = $pat->abilities ?? [];
        if (! in_array('web-login', $abilities, true)) {
            return redirect('/login')->with('error', 'Token non autorisé pour la connexion web');
        }

        $user = $pat->tokenable;
        AuthFacade::login($user);

        return redirect($redirect);
    }
}
