<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Personal;
use Illuminate\Database\Seeder;

final class PersonalSeeder extends Seeder
{
    public function run(): void
    {
        // Crée 5 enseignants fictifs
        for ($i = 1; $i <= 5; $i++) {
            Personal::firstOrCreate([
                'matricule' => 'MAT-PERS-'.$i,
                'name' => 'Enseignant_'.$i,
                'post_name' => 'PostNom_'.$i,
                'pre_name' => 'Prenom_'.$i,
                'email' => 'enseignant'.$i.'@school.com',
                'gender' => 'Masculin',
                'civil_status' => 'Célibataire',
                'country_id' => 1,
                'province_id' => 1,
                'territory_id' => 1,
                'commune_id' => 1,
                'school_id' => 1,
                'type_id' => 1,
                'physical_address' => 'Adresse '.$i,
                'birth_date' => now()->subYears(30 + $i)->toDateString(),
                'birth_place' => 'Ville_'.$i,
                'identity_card_number' => 'IDCARD'.$i,
                'father_id' => null,
                'mother_id' => null,
                'academic_level_id' => 1,
                'phone' => '09900000'.$i,
                'fonction_id' => 1,
                'mechanisation_id' => 1,
            ]);
        }
    }
}
