# Instructions Backend — Global Sync Lock

Afin de protéger l'intégrité des données pendant la synchronisation globale, le frontend a été mis à jour pour "verrouiller" l'application pour tous les utilisateurs. 

Pour que cela fonctionne, **le Frontend attend un comportement réseau spécifique de la part du Backend (Laravel)**.

Voici ce que l'équipe Backend doit implémenter.

---

## 1. Poser et retirer le verrou (Cache)
Dans votre contrôleur existant [app/Sync/SyncController.php](file:///home/paisible/Work/WebstormProjects/IN-AFRICA/PGFEv2-ENABEL/app/Sync/SyncController.php) (autour de la ligne 17), vous devez utiliser le Cache Laravel pour indiquer au reste du système qu'une synchronisation est active.

**Ce qui est attendu :**
- Vérifier si un verrou existe déjà.
- Poser le verrou avant de commencer (avec un timeout de sécurité, ex: 10 minutes).
- **Toujours** retirer le verrou à la fin, même en cas d'erreur (bloc `finally`).

```php
// Fichier : app/Sync/SyncController.php
public function sync(Request $request)
{
    // ... (vos vérifications d'authentification existantes) ...

    // 1. VÉRIFIER LE VERROU
    if (\Illuminate\Support\Facades\Cache::has('system_is_syncing')) {
        return response()->json([
            'success' => false,
            'message' => 'Une synchronisation globale est déjà en cours.'
        ], 409); // 409 Conflict
    }

    // 2. POSER LE VERROU (10 minutes max pour éviter un blocage infini)
    \Illuminate\Support\Facades\Cache::put('system_is_syncing', true, now()->addMinutes(10));

    try {
        // ...
        // TOUT VOTRE CODE DE SYNCHRONISATION ACTUEL ($tables, foreach...)
        // ...

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);

    } finally {
        // 3. TOUJOURS RETIRER LE VERROU À LA FIN
        \Illuminate\Support\Facades\Cache::forget('system_is_syncing');
    }
}
```

---

## 2. Le Endpoint de Statut (Polling)
Pendant que l'utilisateur A synchronise, l'écran de l'utilisateur B est verrouillé. Le frontend de l'utilisateur B va interroger le backend **toutes les 15 secondes** pour savoir quand la synchronisation est terminée.

**Ce qui est attendu :**
Créer une route `GET /api/v1/sync/status` extrêmement légère. 
- Si le verrou existe → Renvoie `503` (Le Frontend continue d'attendre)
- Si le verrou n'existe pas → Renvoie `200` (Le Frontend débloque l'utilisateur)

```php
// Fichier : routes/api.php (ou routes/api/sync.php)
Route::get('/sync/status', function () {
    if (\Illuminate\Support\Facades\Cache::has('system_is_syncing')) {
        return response()->json([
            'message' => 'Une synchronisation globale est en cours.',
            'code' => 'SYNC_IN_PROGRESS'
        ], 503);
    }
    
    return response()->json(['message' => 'Aucune synchronisation en cours.'], 200);
});
```

---

## 3. Le Middleware Global de Protection (Le plus important)
C'est le cœur de la sécurité. Si un utilisateur essaie de modifier une donnée (POST, PUT, DELETE) pendant qu'une synchronisation tourne en tâche de fond, il DOIT être bloqué.

**Ce qui est attendu :**
Créer un Middleware (`php artisan make:middleware CheckSyncLock`). Le Frontend intercepte globalement l'erreur `503` avec le code exact `SYNC_IN_PROGRESS`.

```php
// Fichier : app/Http/Middleware/CheckSyncLock.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckSyncLock
{
    public function handle(Request $request, Closure $next)
    {
        // On protège uniquement les requêtes qui modifient la donnée (par sécurité)
        // Vous pouvez aussi l'étendre aux GET si vous le souhaitez.
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('DELETE')) {
            
            // Si le verrou est actif ET que la requête N'EST PAS la requête de sync elle-même
            if (Cache::has('system_is_syncing') && !$request->is('api/v1/sync*')) {
                return response()->json([
                    'message' => 'Action impossible : Une synchronisation globale est en cours.',
                    'code' => 'SYNC_IN_PROGRESS' // <- CRITIQUE : Le Frontend écoute exactement cette chaîne
                ], 503); // <- CRITIQUE : Le statut HTTP doit être 503
            }
        }

        return $next($request);
    }
}
```

N'oubliez pas d'enregistrer ce middleware dans votre kernel (`app/Http/Kernel.php`) ou dans votre groupe de routes API pour qu'il protège efficacement toutes les routes de l'application.
