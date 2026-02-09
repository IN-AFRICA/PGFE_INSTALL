<?php

namespace App\Sync;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncController extends Controller
{
    /**
     * Endpoint pour la synchronisation bidirectionnelle.
     * Supporte le multi-tenant via school_id de l'utilisateur authentifié.
     */
    public function sync(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $payload = $request->validate([
            'table' => 'required|string',
            'data' => 'required|array',
            'last_sync_at' => 'nullable|date',
        ]);

        $table = $payload['table'];
        $data = $payload['data'];
        $lastSync = $payload['last_sync_at'] ?? null;

        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'school_id')) {
            return response()->json([
                'success' => false,
                'message' => "Table non valide ou non supportée pour la synchronisation multitenant.",
            ], 400);
        }

        // 1. Appliquer les changements montants (Local -> Serveur)
        foreach ($data as $row) {
            if (!isset($row['uuid'])) continue;

            // Protection Multitenant : On force le school_id de l'utilisateur connecté
            $row['school_id'] = $schoolId;
            
            // On retire l'ID auto-incrémenté pour laisser le serveur gérer le sien 
            // ou utiliser l'uuid pour retrouver l'existant.
            $cleanData = collect($row)->except(['id'])->toArray();

            DB::table($table)->updateOrInsert(
                ['uuid' => $row['uuid'], 'school_id' => $schoolId], 
                $cleanData
            );
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
