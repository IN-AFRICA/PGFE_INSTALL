<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SchoolTypeEnum;
use App\Models\Type;
use Illuminate\Database\Seeder;

final class TypeSeeder extends Seeder
{
    public function run(): void
    {
        // Crée (ou récupère) les deux types nécessaires
        foreach (SchoolTypeEnum::cases() as $case) {
            $first = Type::query()->firstOrCreate(['title' => $case->value]);

            // Supprime les doublons éventuels de ce titre (garde le plus ancien / premier)
            Type::query()
                ->where('title', $case->value)
                ->where('id', '!=', $first->id)
                ->delete();
        }

        // Supprime d'éventuels anciens types non conformes
        Type::query()
            ->whereNotIn('title', SchoolTypeEnum::values())
            ->delete();
    }
}
