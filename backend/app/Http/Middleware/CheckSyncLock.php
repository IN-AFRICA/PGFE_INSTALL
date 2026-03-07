<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckSyncLock
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('DELETE')) {
            if (Cache::has('system_is_syncing') && !$request->is('api/v1/sync*')) {
                return response()->json([
                    'message' => 'Action impossible : Une synchronisation globale est en cours.',
                    'code' => 'SYNC_IN_PROGRESS'
                ], 503);
            }
        }
        return $next($request);
    }
}
