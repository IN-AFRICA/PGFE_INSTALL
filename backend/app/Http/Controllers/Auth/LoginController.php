<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class LoginController extends Controller
{
    /** Affiche le formulaire de connexion */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /** Traite la tentative de connexion */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('Identifiants invalides.'),
            ]);
        }

        $request->session()->regenerate();

        // Rediriger tous les utilisateurs authentifiés vers le dashboard admin
        return redirect()->intended(route('admin.dashboard'));
    }

    /** Déconnexion */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Déconnecté.');
    }
}
