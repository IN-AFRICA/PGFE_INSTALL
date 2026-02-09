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
        'filiaires', 'cycles', 'academic_levels', 'classrooms', 
        'courses', 'academic_personals', 'students', 'registrations', 
        'stock_articles', 'stock_categories', 'stock_providers', 'stock_entries', 
        'stock_exits', 'stock_operations', 'payments', 'expenses', 
        'schedules', 'teacher_unavailabilities', 'presences'
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
                ->post($this->remoteUrl . '/api/v1/sync', [
                    'table' => $table,
                    'data' => $localData,
                    'last_sync_at' => $lastSyncAt,
                ]);

            if ($response->successful()) {
                $serverChanges = $response->json('server_changes');
                $syncTime = $response->json('sync_time');

                // 4. Appliquer les changements distants en local (Upsert)
                foreach ($serverChanges as $row) {
                    $cleanRow = collect($row)->except(['id'])->toArray();
                    DB::table($table)->updateOrInsert(
                        ['uuid' => $row['uuid']], 
                        $cleanRow
                    );
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
