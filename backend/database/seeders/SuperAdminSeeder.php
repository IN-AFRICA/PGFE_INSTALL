<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

final class SuperAdminSeeder extends Seeder
{
    /**
     * Create the default super-admin user.
     */
    public function run(): void
    {
        // Ensure super-admin role exists (idempotent)
        $superAdminRole = Role::findOrCreate('super-admin');

        // Get the first school or create one if none exists (idempotent)
        $school = School::first() ?? School::firstOrCreate(
            ['name' => 'École Par Défaut'],
            [
                'address' => 'Adresse par défaut',
                'phone' => '+243000000000',
                'email' => 'ecole@exemple.com',
            ]
        );

        // Create or update super-admin user (idempotent, keyed by unique email)
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@pgfe.com'],
            [
                'name' => 'Super Admin',
                // Align with the message below for clarity
                'password' => Hash::make('SuperAdmin@2025'),
                'school_id' => $school->id,
                'email_verified_at' => now(),
            ]
        );

        // Assign super-admin role if not already assigned
        if (! $superAdmin->hasRole('super-admin')) {
            $superAdmin->assignRole('super-admin');
        }

        $this->command->info('✅ Super-admin créé avec succès !');
        $this->command->info('📧 Email: superadmin@pgfe.com');
        $this->command->info('🔑 Mot de passe: SuperAdmin@2025');
        $this->command->info("⚠️  N'oublie pas de changer le mot de passe en production !");
    }
}
