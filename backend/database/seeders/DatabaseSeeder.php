<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /* User::factory(10)->create();


        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        */

        $this->call([
            // 1. Données de référence géographiques et organisationnelles
            CountrySeeder::class,
            ProvinceSeeder::class,
            TerritorySeeder::class,
            CommuneSeeder::class,
            TypeSeeder::class,
            FonctionSeeder::class,

            // 2. Écoles et structures académiques
            SchoolSeeder::class,
            SemesterSeeder::class,
            CycleSeeder::class,
            // FiliaireSeeder::class, // remplacé par CycleSeeder
            // ClassroomSeeder::class, // remplacé par CycleSeeder
            // AcademicLevelSeeder::class, // remplacé par CycleSeeder

            // 3. Parents et utilisateurs (requis avant AcademicPersonal)
            ParentsSeeder::class,
            RolesAndPermissionsSeeder::class, // Créer les rôles avant les users
            SuperAdminSeeder::class, // Créer le super-admin par défaut
            UserRoleDemoSeeder::class, // Créer des users avec rôles

            // 4. Années scolaires (avant étudiants)
            SchoolYearSeeder::class,

            // 5. Mécanisations (avant Personal)
            MecanisationSeeder::class,

            // 6. Personnel académique (dépend des users)
            AcademicPersonalSeeder::class,
            // 7. Enseignants (doit précéder les cours)
            PersonalSeeder::class,

            // 8. Étudiants (après année scolaire et classes)
            StudentSeeder::class,
            //            FirstYearElectronicsStudentsSeeder::class, // Seeder spécifique : 200 élèves en 1ère Électronique

            // 9. Cours (dépend du personnel académique, enseignants et des classes)
            CourseSeeder::class,

            // 10. Données métier (conduite, cotations, etc.)
            ConduiteSeeder::class,
            ConduiteSemesterSeeder::class,
            FicheCotationSeeder::class,

            // 11. Données financières
            CurrencySeeder::class,
            ExchangeRateSeeder::class,
            AccountSeeder::class,
            // PlanComptableSeeder::class,
            SubAccountSeeder::class,
            FeeTypeSeeder::class,
            FeeSeeder::class,
            PaymentMethodSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
