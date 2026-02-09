<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FeeType;
use Illuminate\Database\Seeder;

final class FeeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Mensuel', 'code' => 'monthly'],
            ['name' => 'Trimestriel', 'code' => 'quarterly'],
            ['name' => 'Annuel', 'code' => 'annual'],
            ['name' => 'Périodique', 'code' => 'periodic'],
            ['name' => 'Circonstanciel', 'code' => 'occasional'],
        ];

        foreach ($types as $type) {
            FeeType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
