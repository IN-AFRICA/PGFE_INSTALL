<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportUsersPasswordsPdf extends Command
{
    protected $signature = 'users:export-pdf';

    protected $description = 'Génère un PDF listant tous les utilisateurs avec leurs rôles et le statut du mot de passe (option 1)';

    public function handle(): int
    {
        // Emails connus des seeders et mots de passe par défaut associés
        $knownPasswords = [
            'superadmin@pgfe.com' => 'SuperAdmin@2025',
            'elvis1@gmail.com' => 'codecode',
            'admin1@gmail.com' => 'codecode',
            'admin-ecole@gmail.com' => 'codecode',
            'admin-ecole2@gmail.com' => 'codecode',
            'tiers@gmail.com' => 'codecode',
            'tiers2@gmail.com' => 'codecode',
            'enseignant1@gmail.com' => 'codecode',
            'comptable1@gmail.com' => 'codecode',
            'stoker1@gmail.com' => 'codecode',
            'rh1@gmail.com' => 'codecode',
            'inspecteur1@gmail.com' => 'codecode',
            'disciplinaire1@gmail.com' => 'codecode',
        ];

        $users = User::with('roles')->orderBy('email')->get();

        $rows = $users->map(function (User $u) use ($knownPasswords) {
            $roles = $u->roles->pluck('name')->values()->all();
            $passwordDisplay = $knownPasswords[$u->email] ?? 'inconnu (hash non récupérable)';

            return [
                'name' => $u->name,
                'email' => $u->email,
                'roles' => $roles,
                'password' => $passwordDisplay,
            ];
        })->all();

        $html = view('exports.users_passwords', [
            'rows' => $rows,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ])->render();

        // Génération du PDF via Dompdf (déjà présent dans le projet)
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'users_passwords_' . now()->format('Ymd_His') . '.pdf';
        $dir = 'exports';

        Storage::disk('local')->makeDirectory($dir);
        Storage::disk('local')->put($dir . '/' . $filename, $dompdf->output());

        $fullPath = storage_path('app/' . $dir . '/' . $filename);
        $this->info('PDF généré: ' . $fullPath);
        $this->info('Note: Les mots de passe non listés sont non récupérables (hash), option 1 respectée.');

        return self::SUCCESS;
    }
}
