<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Country;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommuneRequest;
use App\Models\Commune;
use Illuminate\Http\JsonResponse;

final class CommuneController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = Commune::with('province');

        // Filtre par province
        if ($request->filled('province_id')) {
            $query->where('province_id', (int) $request->input('province_id'));
        }

        // Recherche sur nom (et éventuellement code)
        if ($request->filled('search')) {
            $search = mb_strtolower(mb_trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                if (\Schema::hasColumn('communes', 'code')) {
                    $q->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                }
            });
        }

        $communes = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'data' => $communes,
            'message' => 'Liste des communes récupérée avec succès',
        ]);
    }

    public function store(CommuneRequest $request): JsonResponse
    {
        $commune = Commune::create($request->validated());

        return response()->json([
            'data' => $commune->load('province'),
            'message' => 'Commune créée avec succès',
        ], 201);
    }

    public function update(CommuneRequest $request, Commune $commune): JsonResponse
    {
        $commune->update($request->validated());

        return response()->json([
            'data' => $commune->fresh('province'),
            'message' => 'Commune mise à jour avec succès',
        ]);
    }

    public function destroy(Commune $commune): JsonResponse
    {
        $commune->delete();

        return response()->json([
            'message' => 'Commune supprimée avec succès',
        ]);
    }
}
