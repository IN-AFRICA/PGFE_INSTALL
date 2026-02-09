<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Conduite;
use App\Models\School;
use Illuminate\Database\Seeder;

final class ConduiteSeeder extends Seeder
{
    public function run(): void
    {
        foreach (School::all() as $school) {
            Conduite::firstOrCreate([
                'label' => 'Conduite Générale',
                'school_id' => $school->id,
            ]);
        }
    }
}
