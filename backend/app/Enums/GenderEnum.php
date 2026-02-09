<?php

declare(strict_types=1);

namespace App\Enums;

enum GenderEnum: string
{
    case MA = 'Masculin';

    case FA = 'Féminin';

    case NA = 'Non spécifié';

    public function label(): string
    {
        return match ($this) {
            self::MA => 'Masculin',
            self::FA => 'Féminin',
            self::NA => 'Non spécifié',
        };
    }
}
