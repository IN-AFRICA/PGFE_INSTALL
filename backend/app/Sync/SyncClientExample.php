<?php
// Exemple de script PHP pour synchroniser une table locale avec l'API

use Illuminate\Support\Facades\Http;

// À adapter selon ton contexte Laravel ou PHP natif
function syncTable($table, $localData, $lastSyncAt, $apiUrl, $token = null) {
    $payload = [
        'table' => $table,
        'data' => $localData, // Tableau d'objets/array à synchroniser (avec uuid, updated_at...)
        'last_sync_at' => $lastSyncAt,
    ];
    $headers = [ 'Accept' => 'application/json' ];
    if ($token) $headers['Authorization'] = 'Bearer ' . $token;

    $response = Http::withHeaders($headers)->post($apiUrl, $payload);
    if ($response->successful()) {
        $serverChanges = $response->json('server_changes');
        // Appliquer les changements reçus côté local (upsert)
        // ...
        return $serverChanges;
    } else {
        throw new \Exception('Erreur de synchronisation: ' . $response->body());
    }
}

// Exemple d'appel :
// $serverData = syncTable('stock_articles', $localArticles, $lastSync, 'https://tonserveur/api/v1/sync', 'TOKEN_OPTIONNEL');
