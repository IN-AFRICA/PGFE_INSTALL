<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Parents;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParentRequest;
use App\Models\Parents;
use Illuminate\Http\JsonResponse;

final class ParentController extends Controller
{
    public function index(): JsonResponse
    {
        $parents = Parents::latest()->get();

        return response()->json([
            'data' => $parents,
            'message' => 'Liste des parents récupérée avec succès',
            'success' => true,
            'count' => $parents->count(),
        ]);
    }
    public function show(Parents $parent): JsonResponse
    {
        $parent->load(['students']);

        return response()->json([
            'data' => $parent,
            'message' => 'Parent récupéré avec succès',
            'success' => true,
        ]);
    }

    public function store(ParentRequest $request): JsonResponse
    {
        \Log::info('ParentController@store data', $request->all());
        $parent = Parents::create($request->validated());

        return response()->json([
            'data' => $parent,
            'message' => 'Parent créé avec succès',
        ], 201);
    }

    public function update(ParentRequest $request, Parents $parent): JsonResponse
    {
        $parent->update($request->validated());

        return response()->json([
            'data' => $parent->fresh(),
            'message' => 'Parent mis à jour avec succès',
        ]);
    }

    public function destroy(Parents $parent): JsonResponse
    {
        $parent->delete();

        return response()->json([
            'message' => 'Parent supprimé avec succès',
        ]);
    }
}
