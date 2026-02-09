<?php

declare(strict_types=1);

namespace App\Enums;

enum DisciplineLevelEnum: string
{
    case BONNE_CONDUITE = 'Bonne conduite';

    case EXCELLENTE_CONDUITE = 'Excellente conduite';

    case CONDUITE_PASSABLE = 'Conduite passable';

    case CONDUITE_MAUVAIS = 'Conduite mauvais';

    case CONDUITE_TRES_MAUVAIS = 'Conduite très mauvais';

    case CONDUITE_INSUFFISANTE = 'Conduite insuffisante';

    case CONDUITE_TRES_INSUFFISANTE = 'Conduite très insuffisante';

    public function label(): string
    {
        return match ($this) {
            self::BONNE_CONDUITE => 'Bonne conduite',
            self::EXCELLENTE_CONDUITE => 'Excellente conduite',
            self::CONDUITE_PASSABLE => 'Conduite passable',
            self::CONDUITE_MAUVAIS => 'Conduite mauvais',
            self::CONDUITE_TRES_MAUVAIS => 'Conduite très mauvais',
            self::CONDUITE_INSUFFISANTE => 'Conduite insuffisante',
            self::CONDUITE_TRES_INSUFFISANTE => 'Conduite très insuffisante',
        };
    }
}
