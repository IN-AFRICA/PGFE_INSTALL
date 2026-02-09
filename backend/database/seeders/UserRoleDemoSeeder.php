<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\School;
use App\Models\Type;
use App\Models\User;
use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

final class UserRoleDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Rendez le seeder idempotent pour éviter les erreurs d'unicité
        // 1) S'assurer que TOUS les rôles utilisés existent
        $roles = [
            'admin',
            'admin-ecole',
            'tiers',
            'enseignant',
            'comptable',
            'stoker',
            'rh',
            'inspecteur',
            'disciplinaire',
        ];
        foreach ($roles as $r) {
            Role::findOrCreate($r);
        }

        // 2) Entités de référence: utiliser firstOrCreate pour respecter les contraintes d'unicité
        $country = Country::query()->firstOrCreate([
            'name' => 'RD Congo',
        ]);

        // Assurer qu'au moins un type existe (Formel / Non formel)
        $type = Type::query()->firstOrCreate(['title' => 'Formel']);

        // Créer (ou récupérer) les écoles de démonstration de façon idempotente
        $schoolMain = School::query()->firstOrCreate(
            ['name' => 'École Démonstration 1'],
            [
                'country_id' => $country->id, // Adapter si nécessaire ou créer un pays avant
                'city' => 'Kinshasa',
                'address' => 'Avenue Exemple 123',
                'type_id' => $type->id,
            ]
        );

        // Optionnel : une seconde école pour illustrer admin global multi-écoles
        $schoolSecondary = School::query()->firstOrCreate(
            ['name' => 'École Démonstration 2'],
            [
                'country_id' => $schoolMain->country_id ?? $country->id,
                'city' => 'Lubumbashi',
                'address' => 'Boulevard Test 456',
                'type_id' => $type->id,
            ]
        );

        // Helper de création avec affectation école + rôle
        $makeUser = function (string $email, string $name, string $role, School $school) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('codecode'),
                    'school_id' => $school->id,
                ]
            );

            // Si l'utilisateur existe déjà mais sans école, on l'affecte
            if (empty($user->school_id)) {
                $user->school_id = $school->id;
                $user->save();
            }

            if (! $user->hasRole($role)) {
                // On ne mélange pas les rôles ici pour rester simple; sync assure que seul ce rôle est associé
                $user->syncRoles([$role]);
            }

            return $user;
        };

        // Admin global (attaché à la première école mais scope non restreint grâce au rôle admin)
        $admin1 = $makeUser('elvis1@gmail.com', 'Admin Principal', 'admin', $schoolMain);
        $admin2 = $makeUser('admin1@gmail.com', 'Admin Principal 2', 'admin', $schoolSecondary);

        // Admin école (restreint à son école via ScopeBySchool)
        $adminEcole1 = $makeUser('admin-ecole@gmail.com', 'Admin École Démo', 'admin-ecole', $schoolMain);
        $adminEcole2 = $makeUser('admin-ecole2@gmail.com', 'Admin École Démo 2', 'admin-ecole', $schoolSecondary);

        // Tiers (lecture seule)
        $tiers1 = $makeUser('tiers@gmail.com', 'Utilisateur Tiers', 'tiers', $schoolMain);
        $tiers2 = $makeUser('tiers2@gmail.com', 'Utilisateur Tiers 2', 'tiers', $schoolSecondary);

        // Autres rôles avec mot de passe par défaut "codecode"
        $enseignant1 = $makeUser('enseignant1@gmail.com', 'Enseignant Démo 1', 'enseignant', $schoolMain);
        $comptable1 = $makeUser('comptable1@gmail.com', 'Comptable Démo 1', 'comptable', $schoolMain);
        $stoker1 = $makeUser('stoker1@gmail.com', 'Stoker Démo 1', 'stoker', $schoolMain);
        $rh1 = $makeUser('rh1@gmail.com', 'RH Démo 1', 'rh', $schoolMain);
        $inspecteur1 = $makeUser('inspecteur1@gmail.com', 'Inspecteur Démo 1', 'inspecteur', $schoolSecondary);
        $disciplinaire1 = $makeUser('disciplinaire1@gmail.com', 'Disciplinaire Démo 1', 'disciplinaire', $schoolMain);

        // Log facultatif (désactivé pour éviter bruit en prod)
        // info('UserRoleDemoSeeder: comptes créés/actualisés', [
        //     'admin_global_1' => $admin1->id,
        //     'admin_global_2' => $admin2->id,
        //     'admin_ecole' => $adminEcole->id,
        //     'tiers' => $tiers->id,
        // ]);
    }
}
