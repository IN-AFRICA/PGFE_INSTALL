<?php

declare(strict_types=1);

namespace App\Enums;

enum SchoolTypeEnum: string
{
    case FORMEL = 'Formel';
    case NON_FORMEL = 'Non formel';

    /**
     * Return all enum string values.
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::FORMEL => 'Formel',
            self::NON_FORMEL => 'Non formel',
        };
    }
}
