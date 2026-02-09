<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Fonctions;

use App\Http\Controllers\Controller;
use App\Http\Requests\FonctionRequest;
use App\Models\Fonction;
use Illuminate\Http\JsonResponse;

final class FonctionController extends Controller
{
    public function index(): JsonResponse
    {
        $fonctions = Fonction::latest()->get();

        return response()->json([
            'data' => $fonctions,
            'message' => 'Liste des fonctions récupérée avec succès',
        ]);
    }

    public function store(FonctionRequest $request): JsonResponse
    {
        $fonction = Fonction::create($request->validated());

        return response()->json([
            'data' => $fonction,
            'message' => 'Fonction créée avec succès',
        ], 201);
    }

    public function update(FonctionRequest $request, Fonction $fonction): JsonResponse
    {
        $fonction->update($request->validated());

        return response()->json([
            'data' => $fonction->fresh(),
            'message' => 'Fonction mise à jour avec succès',
        ]);
    }

    public function destroy(Fonction $fonction): JsonResponse
    {
        $fonction->delete();

        return response()->json([
            'message' => 'Fonction supprimée avec succès',
        ]);
    }
}
