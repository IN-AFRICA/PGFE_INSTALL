<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Fournit une méthode helper pour générer dynamiquement la règle de validation de school_id.
 * - Admin (rôle 'admin'): conserve la règle transmise (required / sometimes ...)
 * - Autres utilisateurs: interdit explicitement l'envoi (prohibited)
 */
trait SchoolIdRule
{
    protected function schoolIdRule(string $adminRule): string
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return $adminRule; // admin conserve la règle originale
        }

        return 'prohibited'; // les autres ne peuvent pas soumettre school_id
    }
}
