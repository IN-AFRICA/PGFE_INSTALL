<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\School;
use Illuminate\Database\Seeder;

final class AccountSeeder extends Seeder
{
    public function run(): void
    {
        foreach (School::all() as $school) {
            Account::updateOrCreate([
                'school_id' => $school->id,
            ], [
                'name' => 'Compte principal '.$school->name,
                'number' => 'ACC-'.$school->id.'-'.uniqid(),
                'code' => 'CODE-'.$school->id.'-'.uniqid(),
                'user_id' => \App\Models\User::query()->first()?->id,
                // Ajoute ici d’autres champs obligatoires si nécessaire
            ]);
        }
    }
}
