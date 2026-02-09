<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Nettoyage (idempotent)
        app()['cache']->forget('spatie.permission.cache');

        // Granular permissions
        $permissions = [
            // Users
            'users.view', 'users.create.tiers', 'users.create.any', 'users.update', 'users.delete', 'users.assign.roles',
            // Schools & years
            'schools.view', 'schools.create', 'schools.update', 'schools.delete',
            'schoolyears.view', 'schoolyears.create', 'schoolyears.update', 'schoolyears.delete', 'schoolyears.activate',
            // Academic structures
            'classrooms.full', 'academic-levels.full', 'students.full', 'students.view', 'personals.full',
            'classrooms.view', 'courses.manage', 'grades.manage', 'deliberation.manage',
            // Finance & Accounting
            'accounts.full', 'fees.full', 'payments.full', 'expenses.full', 'accounting.module',
            // Stock & Asset Management
            'stock.full', 'sales.full', 'rentals.full',
            // HR / Personnel
            'personnel.module',
            // Inspecteur
            'visits.manage',
            // Discipline
            'discipline.full',
            // Infrastructure
            'infrastructure.full',
            // Settings
            'settings.full',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Rôles standards existants
        $admin = Role::firstOrCreate(['name' => 'super-admin']);
        $adminEcole = Role::firstOrCreate(['name' => 'admin-ecole']);
        $tiers = Role::firstOrCreate(['name' => 'tiers']);

        // Nouveaux rôles demandés
        $enseignant = Role::firstOrCreate(['name' => 'enseignant']);
        $comptable = Role::firstOrCreate(['name' => 'comptable']);
        $stoker = Role::firstOrCreate(['name' => 'stoker']);
        $rh = Role::firstOrCreate(['name' => 'rh']);
        $inspecteur = Role::firstOrCreate(['name' => 'inspecteur']);
        $disciplinaire = Role::firstOrCreate(['name' => 'disciplinaire']);

        // SUPER-ADMIN: toutes les permissions
        $admin->syncPermissions(Permission::all());

        // ADMIN-ECOLE: Gestion locale
        $adminEcolePermissions = [
            'users.view', 'users.update', 'users.create.tiers',
            'schools.view',
            'schoolyears.view', 'schoolyears.activate',
            'students.full', 'personals.full',
            'accounts.full', 'fees.full', 'payments.full', 'expenses.full',
            'infrastructure.full', 'stock.full', 'personnel.module',
            'courses.manage', 'grades.manage', 'deliberation.manage',
            'discipline.full', 'visits.manage',
        ];
        $adminEcole->syncPermissions($adminEcolePermissions);

        // ENSEIGNANT: Cours, cotation et délibération
        $enseignant->syncPermissions([
            'courses.manage',
            'grades.manage',
            'deliberation.manage',
            'students.view',
        ]);

        // COMPTABLE: Module comptabilité
        $comptable->syncPermissions([
            'accounts.full',
            'fees.full',
            'payments.full',
            'expenses.full',
            'accounting.module',
        ]);

        // STOKER: Stock, vente et location
        $stoker->syncPermissions([
            'stock.full',
            'sales.full',
            'rentals.full',
        ]);

        // RH: Module Personnel
        $rh->syncPermissions([
            'personnel.module',
            'personals.full',
        ]);

        // INSPECTEUR: Visite de classe
        $inspecteur->syncPermissions([
            'visits.manage',
            'classrooms.view',
        ]);

        // DISCIPLINAIRE: Toute la discipline
        $disciplinaire->syncPermissions([
            'discipline.full',
            'students.view',
        ]);

        // TIERS: uniquement lecture
        $tiers->syncPermissions([]);

        // Super admin seed
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'password');

        $super = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrateur Global',
                'password' => Hash::make($password),
            ]
        );
        $super->assignRole('super-admin');
    }
}
