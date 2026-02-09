<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Country;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProvinceRequest;
use App\Models\Province;
use Illuminate\Http\JsonResponse;

final class ProvinceController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = Province::with('country');

        // Filtre par pays
        if ($request->filled('country_id')) {
            $query->where('country_id', (int) $request->input('country_id'));
        }

        // Recherche sur nom (et éventuellement code)
        if ($request->filled('search')) {
            $search = mb_strtolower(mb_trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                if (\Schema::hasColumn('provinces', 'code')) {
                    $q->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                }
            });
        }

        $provinces = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'data' => $provinces,
            'message' => 'Liste des provinces récupérée avec succès',
        ]);
    }

    public function store(ProvinceRequest $request): JsonResponse
    {
        $province = Province::create($request->validated());

        return response()->json([
            'data' => $province->load('country'),
            'message' => 'Province créée avec succès',
        ], 201);
    }

    public function update(ProvinceRequest $request, Province $province): JsonResponse
    {
        $province->update($request->validated());

        return response()->json([
            'data' => $province->fresh('country'),
            'message' => 'Province mise à jour avec succès',
        ]);
    }

    public function destroy(Province $province): JsonResponse
    {
        try {
            // Supprimer les enregistrements liés (ex: academic_personals) avant de supprimer la province
            if (method_exists($province, 'academicPersonals')) {
                $province->academicPersonals()->delete();
            }
            $province->delete();
            return response()->json([
                'message' => 'Province supprimée avec succès',
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Une erreur est survenue lors de la suppression de la province.",
                'success' => false
            ], 500);
        }
    }
}
