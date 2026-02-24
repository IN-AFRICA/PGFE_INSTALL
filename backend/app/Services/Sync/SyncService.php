<?php

namespace App\Services\Sync;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncService
{
    protected $remoteUrl;
    protected $apiToken;
    protected $tables = [
        // Tables réellement supportées par la synchro multitenant (présence de school_id + uuid côté serveur)

        // Coeur académique / structure
        'schools',
        'filiaires',
        'cycles',
        'academic_levels',
        'classrooms',
        'courses',
        'academic_personals',
        'students',
        'registrations',

        // Finance & caisse
        'fees',
        'payments',
        'expenses',

        // Comptabilité (plan et mouvements)
        'account_plan',
        'sub_account_plan',
        'account_numbers',
        'account_types',
        'InputAccount',
        'OutputAccount',
        'journals',
        'credits',
        'debits',

        // Stock
        'stock_articles',
        'stock_categories',
        'stock_providers',
        'stock_entries',
        'stock_exits',
        'stock_operations',
        'stock_states',
        'stock_inventories',

        // Planning & présence
        'schedules',
        // 'teacher_unavailabilities', // non supportée côté HUB pour l'instant
        'presences',

        // Résultats académiques
        'fiche_cotations',
        'deliberations',
        'repechages',

        // Validation des lauréats
        'validation_aureats',

        // Parents / tuteurs
        'parents',

        // Tables satellites du module étudiant
        'student_transfers',
        'student_exits',
        'student_activities',
        'registration_parents',

        // Module infrastructures
        'infra_categories',
        'infra_infrastructures',
        'infra_equipements',
        'infra_inventaires',
        'infra_etats',
        'infra_inventory_equipment',
        'infra_inventory_real_states',

        // Module rental / projets
        'equipments',
        'clients',
        'projects',
        'rental_contracts',
        'rental_contract_equipment',
        'rental_payments',
    ];

    public function __construct()
    {
        $this->remoteUrl = config('services.sync.remote_url');
        $this->apiToken = config('services.sync.api_token');
    }

    /**
     * Synchronise toutes les tables définies.
     */
    public function syncAll()
    {
        if (!$this->remoteUrl) {
            Log::error("SyncService: REMOTE_URL non défini.");
            return false;
        }

        foreach ($this->tables as $table) {
            $this->syncTable($table);
        }

        return true;
    }

    /**
     * Synchronise une table spécifique.
     */
    public function syncTable($table)
    {
        try {
            // 1. Récupérer la date de dernière synchro (stockée en local)
            $lastSyncAt = DB::table('sync_logs')->where('table_name', $table)->value('last_sync_at');

            // 2. Récupérer les données locales modifiées depuis lastSyncAt
            $query = DB::table($table);
            if ($lastSyncAt) {
                $query->where('updated_at', '>', $lastSyncAt);
            }
            $localData = $query->get();

            // 3. Envoyer au serveur et recevoir les changements distants
            $response = Http::withToken($this->apiToken)
                ->acceptJson()
                ->timeout(120) // augmenter le timeout pour les grosses tables (ex: students)
                ->post($this->remoteUrl . '/api/v1/sync', [
                    'table' => $table,
                    'data' => $localData->toArray(),
                    'last_sync_at' => $lastSyncAt,
                ]);

            if ($response->successful()) {
                $serverChanges = $response->json('server_changes');
                $syncTime = $response->json('sync_time');

                // 4. Appliquer les changements distants en local (Upsert)
                foreach ($serverChanges as $row) {
                    $cleanRow = collect($row)->except(['id'])->toArray();

                    try {
                        // Protection FK simple pour certaines tables
                        if ($table === 'registrations' && isset($cleanRow['student_id'])) {
                            $studentExists = DB::table('students')
                                ->where('id', $cleanRow['student_id'])
                                ->exists();

                            if (! $studentExists) {
                                Log::warning('SyncService: ligne registration ignorée, student inexistant', [
                                    'uuid' => $row['uuid'] ?? null,
                                    'student_id' => $cleanRow['student_id'],
                                ]);

                                continue;
                            }
                        }

                        if ($table === 'academic_personals' && isset($cleanRow['country_id'])) {
                            $countryExists = DB::table('countries')
                                ->where('id', $cleanRow['country_id'])
                                ->exists();

                            if (! $countryExists) {
                                $defaultCountryId = DB::table('countries')->min('id');

                                if ($defaultCountryId) {
                                    $cleanRow['country_id'] = $defaultCountryId;
                                }
                            }
                        }

                        DB::table($table)->updateOrInsert(
                            ['uuid' => $row['uuid']],
                            $cleanRow
                        );
                    } catch (\Throwable $rowException) {
                        Log::error('SyncService: Ligne ignorée pour '.$table.' : '.$rowException->getMessage(), [
                            'row' => $row,
                        ]);

                        continue;
                    }
                }

                // 5. Mettre à jour le log de synchro
                DB::table('sync_logs')->updateOrInsert(
                    ['table_name' => $table],
                    ['last_sync_at' => $syncTime]
                );

                Log::info("SyncService: Table $table synchronisée avec succès.");
            } else {
                Log::error("SyncService: Échec de synchro pour $table : " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("SyncService: Erreur lors de la synchro de $table : " . $e->getMessage());
        }
    }
}
