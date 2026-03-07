<?php

use Illuminate\Support\Facades\Route;
use App\Sync\SyncController;


use Illuminate\Support\Facades\Cache;
Route::post('/sync', [SyncController::class, 'sync']);

Route::get('/sync/status', function () {
	if (Cache::has('system_is_syncing')) {
		return response()->json([
			'message' => 'Une synchronisation globale est en cours.',
			'code' => 'SYNC_IN_PROGRESS'
		], 503);
	}
	return response()->json(['message' => 'Aucune synchronisation en cours.'], 200);
});
