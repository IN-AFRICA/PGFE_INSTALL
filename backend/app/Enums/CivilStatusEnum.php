<?php

declare(strict_types=1);

namespace App\Enums;

enum CivilStatusEnum: string
{
    case C = 'Célibataire';

    case M = 'Marié(e)';

    case D = 'Divorcé(e)';

    case V = 'Veuf/Veuve';

    public function label(): string
    {
        return match ($this) {
            self::C => 'Célibataire',
            self::M => 'Marié(e)',
            self::D => 'Divorcé(e)',
            self::V => 'Veuf/Veuve',
        };
    }
}
