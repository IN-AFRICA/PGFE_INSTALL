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

        // Liste complète des tables à synchroniser (bidirectionnel)
        $tables = [
            'students',
            'academic_personals',
            'classrooms',
            'cycles',
            'courses',
            'fees',
            'account_plans',
            'academic_levels',
            'stock_articles',
            'stock_categories',
            'stock_providers',
            'stock_states',
            'stock_entries',
            'stock_exits',
            'infra_inventories',
            'infra_equipments',
            'infra_inventory_equipments',
            'exercices',
            'formation_continues',
            'person_conges',
            'rental_contract_equipments',
            // Ajoute ici toute autre table synchronisée
        ];

        $results = [];
        foreach ($tables as $table) {
            $lastSync = null; // Initialisation pour chaque table
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'school_id')) {
                $results[$table] = [
                    'success' => false,
                    'message' => "Table non valide ou non supportée pour la synchronisation multitenant.",
                ];
                continue;
            }

            // Synchronisation bidirectionnelle
            // 1. Synchronisation montante (Local -> Serveur)
            // On récupère les vraies données locales de la base pour chaque table (par school_id)
            $data = [];
            try {
                $data = DB::table($table)->where('school_id', $schoolId)->get();
            } catch (\Exception $e) {
                // Si la table n'existe pas ou autre erreur, on saute la synchro montante
                $results[$table] = [
                    'success' => false,
                    'message' => "Erreur lors de la récupération des données locales : " . $e->getMessage(),
                ];
                continue;
            }
            if ($data && (is_array($data) || $data instanceof \Illuminate\Support\Collection) && count($data) > 0) {
                foreach ($data as $row) {
                    $row = (array) $row;
                    if (! isset($row['uuid'])) {
                        continue;
                    }
                    $row['school_id'] = $schoolId;
                    $cleanData = collect($row)->except(['id'])->toArray();
                    try {
                        if ($table === 'students' && isset($row['matricule'])) {
                            $existing = DB::table($table)
                                ->where('school_id', $schoolId)
                                ->where('matricule', $row['matricule'])
                                ->first();
                            if ($existing) {
                                DB::table($table)
                                    ->where('id', $existing->id)
                                    ->update($cleanData);
                                continue;
                            }
                        }
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
                        if ($table === 'students' && isset($row['matricule'])) {
                            DB::table($table)
                                ->where('school_id', $schoolId)
                                ->where('matricule', $row['matricule'])
                                ->update($cleanData);
                            continue;
                        }
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
            }

            // 2. Synchronisation descendante (Serveur -> Local)
            $lastSync = null; // Initialisation pour éviter l'erreur Undefined variable
            $query = DB::table($table)->where('school_id', $schoolId);
            if ($lastSync) {
                $query->where('updated_at', '>', $lastSync);
            }
            $serverChanges = $query->get();
            $results[$table] = [
                'success' => true,
                'server_changes' => $serverChanges,
                'sync_time' => now()->toDateTimeString(),
            ];
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }
}
