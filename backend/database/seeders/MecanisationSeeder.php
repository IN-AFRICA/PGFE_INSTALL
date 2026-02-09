<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Mecanisation;
use Illuminate\Database\Seeder;

final class MecanisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Mecanisation::create([
            'label' => 'Automatisation',
            'description' => 'Processus d’automatisation des tâches administratives',
        ]);
        Mecanisation::create([
            'label' => 'Numérisation',
            'description' => 'Transformation des documents papier en format numérique',
        ]);
        // Ajoutez d’autres seeds si nécessaire
    }
}
