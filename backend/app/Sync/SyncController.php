<?php

namespace App\Sync;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\UniqueConstraintViolationException;

class SyncController extends Controller
{
    /**
     * Endpoint pour la synchronisation bidirectionnelle.
     * Supporte le multi-tenant via school_id de l'utilisateur authentifié.
     */
    public function sync(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié pour la synchronisation.',
            ], 401);
        }

        if (is_null($user->school_id)) {
            return response()->json([
                'success' => false,
                'message' => "Aucun school_id associé à l'utilisateur pour la synchronisation multitenant.",
            ], 400);
        }

        $schoolId = (int) $user->school_id;

        $payload = $request->validate([
            'table' => 'required|string',
            'data' => 'sometimes|array',
            'last_sync_at' => 'nullable|date',
        ]);

        $table = $payload['table'];
        $data = $payload['data'] ?? [];
        $lastSync = $payload['last_sync_at'] ?? null;

        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'school_id')) {
            return response()->json([
                'success' => false,
                'message' => "Table non valide ou non supportée pour la synchronisation multitenant.",
            ], 400);
        }

        // 1. Appliquer les changements montants (Local -> Serveur)
        foreach ($data as $row) {
            if (! isset($row['uuid'])) {
                continue;
            }

            // Protection Multitenant : On force le school_id de l'utilisateur connecté
            $row['school_id'] = $schoolId;

            // On retire l'ID auto-incrémenté pour laisser le serveur gérer le sien
            $cleanData = collect($row)->except(['id'])->toArray();

            try {
                // Cas particulier: students -> éviter les doublons de matricule
                if ($table === 'students' && isset($row['matricule'])) {
                    $existing = DB::table($table)
                        ->where('school_id', $schoolId)
                        ->where('matricule', $row['matricule'])
                        ->first();

                    if ($existing) {
                        // On met à jour l'élève existant au lieu d'insérer un doublon
                        DB::table($table)
                            ->where('id', $existing->id)
                            ->update($cleanData);

                        continue;
                    }
                }

                // Cas particulier: academic_personals -> éviter les doublons d'email
                if ($table === 'academic_personals' && isset($row['email'])) {
                    $existing = DB::table($table)
                        ->where('school_id', $schoolId)
                        ->where('email', $row['email'])
                        ->first();

                    if ($existing) {
                        DB::table($table)
                            ->where('id', $existing->id)
                            ->update($cleanData);

                        continue;
                    }
                }

                DB::table($table)->updateOrInsert(
                    ['uuid' => $row['uuid'], 'school_id' => $schoolId],
                    $cleanData
                );
            } catch (UniqueConstraintViolationException $e) {
                // Filet de sécurité: si malgré tout on tombe sur un doublon de matricule pour students,
                // on fait une mise à jour sur la base du matricule au lieu d'un insert.
                if ($table === 'students' && isset($row['matricule'])) {
                    DB::table($table)
                        ->where('school_id', $schoolId)
                        ->where('matricule', $row['matricule'])
                        ->update($cleanData);

                    continue;
                }

                // Filet de sécurité: éviter les doublons d'email pour academic_personals
                if ($table === 'academic_personals' && isset($row['email'])) {
                    DB::table($table)
                        ->where('school_id', $schoolId)
                        ->where('email', $row['email'])
                        ->update($cleanData);

                    continue;
                }

                throw $e;
            }
        }

        // 2. Récupérer les changements descendants (Serveur -> Local)
        $query = DB::table($table)->where('school_id', $schoolId);
        
        if ($lastSync) {
            $query->where('updated_at', '>', $lastSync);
        }

        $serverChanges = $query->get();

        return response()->json([
            'success' => true,
            'server_changes' => $serverChanges,
            'sync_time' => now()->toDateTimeString(),
        ]);
    }
}
