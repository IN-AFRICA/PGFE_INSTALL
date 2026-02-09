<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloque toutes les requêtes non-lecture pour les utilisateurs ayant uniquement le rôle viewer (tiers).
 */
final class RestrictReadOnlyRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // Méthodes lecture autorisées
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $user = $request->user();
        if ($user && $user->hasRole('tiers') && ! $user->hasAnyRole(['admin', 'admin-ecole'])) {
            return response()->json([
                'message' => 'Action non autorisée pour un rôle lecture seule.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
