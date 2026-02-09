<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireSelectedSchool
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->hasRole('super-admin')) {
            if (! session('selected_school_id')) {
                return redirect()->route('admin.dashboard')
                    ->with('error', "Veuillez d'abord sélectionner une école pour accéder à cette section.");
            }
        }

        return $next($request);
    }
}
