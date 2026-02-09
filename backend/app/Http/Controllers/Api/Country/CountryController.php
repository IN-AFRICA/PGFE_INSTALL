<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Country;

use App\Http\Controllers\Controller;
use App\Http\Requests\CountryRequest;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CountryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Country::query();

        // Recherche filtrante sur nom et code éventuel
        if ($request->filled('search')) {
            $search = mb_strtolower(mb_trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                if (\Schema::hasColumn('countries', 'code')) {
                    $q->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                }
            });
        }

        $countries = $query->orderBy('name')->get();

        return response()->json([
            'data' => $countries,
            'message' => 'Liste des pays récupérée avec succès',
        ]);
    }

    public function store(CountryRequest $request): JsonResponse
    {
        $country = Country::create($request->validated());

        return response()->json([
            'data' => $country,
            'message' => 'Pays créé avec succès',
        ], 201);
    }

    public function update(CountryRequest $request, Country $country): JsonResponse
    {
        $country->update($request->validated());

        return response()->json([
            'data' => $country->fresh(),
            'message' => 'Pays mis à jour avec succès',
        ]);
    }

    public function destroy(Country $country): JsonResponse
    {
        $country->delete();

        return response()->json([
            'message' => 'Pays supprimé avec succès',
        ]);
    }
}
