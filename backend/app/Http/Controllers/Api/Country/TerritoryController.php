<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Country;

use App\Http\Controllers\Controller;
use App\Http\Requests\TerritoryRequest;
use App\Models\Territory;
use Illuminate\Http\JsonResponse;

final class TerritoryController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = Territory::with('province');

        // Filtre par province
        if ($request->filled('province_id')) {
            $query->where('province_id', (int) $request->input('province_id'));
        }

        // Recherche sur nom (et éventuellement code)
        if ($request->filled('search')) {
            $search = mb_strtolower(mb_trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                if (\Schema::hasColumn('territories', 'code')) {
                    $q->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                }
            });
        }

        $territories = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'data' => $territories,
            'message' => 'Liste des territoires récupérée avec succès',
        ]);
    }

    public function store(TerritoryRequest $request): JsonResponse
    {
        $territory = Territory::create($request->validated());

        return response()->json([
            'data' => $territory->load('province'),
            'message' => 'Territoire créé avec succès',
        ], 201);
    }

    public function update(TerritoryRequest $request, Territory $territory): JsonResponse
    {
        $territory->update($request->validated());

        return response()->json([
            'data' => $territory->fresh('province.country'),
            'message' => 'Territoire mis à jour avec succès',
        ]);
    }

    public function destroy(Territory $territory): JsonResponse
    {
        $territory->delete();

        return response()->json([
            'message' => 'Territoire supprimé avec succès',
        ]);
    }
}
