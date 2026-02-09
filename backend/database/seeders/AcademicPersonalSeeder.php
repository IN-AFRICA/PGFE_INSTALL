<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AcademicLevel;
use App\Models\AcademicPersonal;
use App\Models\Commune;
use App\Models\Country;
use App\Models\Fonction;
use App\Models\Parents;
use App\Models\Province;
use App\Models\School;
use App\Models\Territory;
use App\Models\Type;
use App\Models\User;
use Illuminate\Database\Seeder;

final class AcademicPersonalSeeder extends Seeder
{
    public function run(): void
    {
        if (AcademicPersonal::query()->exists()) {
            return; // avoid duplicating large dataset
        }

        // Vérifier que toutes les données de référence existent
        $countries = Country::all();
        $provinces = Province::all();
        $territories = Territory::all();
        $communes = Commune::all();
        $schools = School::all();
        $types = Type::all();
        $parents = Parents::all();
        $academicLevels = AcademicLevel::all();
        $fonctions = Fonction::all();
        $users = User::all();

        // Vérifier que nous avons au moins un élément de chaque
        if ($countries->isEmpty() || $provinces->isEmpty() || $territories->isEmpty() ||
            $communes->isEmpty() || $schools->isEmpty() || $types->isEmpty() ||
            $academicLevels->isEmpty() || $fonctions->isEmpty() || $users->isEmpty()) {

            $this->command->warn('Skipping AcademicPersonalSeeder: Missing required reference data');

            return;
        }

        AcademicPersonal::factory()
            ->count(min(50, $users->count())) // Ne pas créer plus d'AcademicPersonal que d'Users
            ->create()
            ->each(function (AcademicPersonal $academicPersonal) use (
                $countries,
                $provinces,
                $territories,
                $communes,
                $schools,
                $types,
                $parents,
                $academicLevels,
                $fonctions,
                $users,
            ): void {
                $academicPersonal->update([
                    'country_id' => $countries->random()->id,
                    'province_id' => $provinces->random()->id,
                    'territory_id' => $territories->random()->id,
                    'commune_id' => $communes->random()->id,
                    'school_id' => $schools->random()->id,
                    'type_id' => $types->random()->id,
                    'father_id' => $parents->isNotEmpty() ? $parents->random()->id : null,
                    'mother_id' => $parents->isNotEmpty() ? $parents->random()->id : null,
                    'academic_level_id' => $academicLevels->random()->id,
                    'fonction_id' => $fonctions->random()->id,
                    'user_id' => $users->random()->id,
                ]);
            });

        $personnels = [
            ['name' => 'Rooney Kalumba', 'email' => 'rooney.kalumba@example.com'],
            ['name' => 'Elvis Kankola', 'email' => 'elvis.kankola@example.com'],
            ['name' => 'Youri Mukaz', 'email' => 'youri.mukaz@example.com'],
            ['name' => 'Augustin Paisible', 'email' => 'augustin.paisible@example.com'],
            ['name' => 'Mervadie', 'email' => 'mervadie@example.com'],
            ['name' => 'Jonathan', 'email' => 'jonathan@example.com'],
        ];
        foreach ($personnels as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt('password'),
            ]);
            AcademicPersonal::forceCreate([
                'user_id' => $user->id,
                'country_id' => $countries->first()->id,
                'province_id' => $provinces->first()->id,
                'territory_id' => $territories->first()->id,
                'commune_id' => $communes->first()->id,
                'school_id' => $schools->first()->id,
                'type_id' => $types->first()->id,
                'father_id' => $parents->first()->id ?? null,
                'mother_id' => $parents->first()->id ?? null,
                'academic_level_id' => $academicLevels->first()->id,
                'fonction_id' => $fonctions->first()->id,
                'matricule' => 'MAT'.uniqid(),
                'name' => $data['name'],
                'post_name' => $data['name'],
                'pre_name' => $data['name'],
                'username' => mb_strtolower(str_replace(' ', '', $data['name'])),
                'phone' => '099'.rand(1000000, 9999999),
                'email' => $data['email'],
                'identity_card_number' => 'ID'.uniqid(),
                'gender' => 'Masculin',
                'civil_status' => 'Célibataire',
                'physical_address' => 'Adresse '.$data['name'],
                'birth_date' => '1990-01-01',
                'birth_place' => 'Kinshasa',
            ]);
        }
    }
}
