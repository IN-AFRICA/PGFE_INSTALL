<?php

declare(strict_types=1);

namespace App\Support;

final class Hook
{
    /**
     * Applique des filtres sur une valeur (dummy).
     *
     * @param  string  $tag
     * @param  mixed  $value
     * @return mixed
     */
    public static function applyFilters($tag, $value)
    {
        // Ici, tu peux ajouter la logique de filtrage si besoin
        return $value;
    }
}
