<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Infrastructure;

use App\Http\Controllers\Controller;
use App\Http\Requests\InfraInventaireRequest;
use App\Http\Resources\InfraInventaireResource;
use App\Models\InfraInventaire;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class InfraInventaireController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection|Response
    {
        try {
            $inventaires = InfraInventaire::all();
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => InfraInventaireResource::collection($inventaires),
            'success' => true,
            'message' => 'Liste des inventaires d\'infrastructures récupérée avec succès',
        ]);
    }

    public function store(InfraInventaireRequest $request): Response
    {
        try {
            $infraInventaire = InfraInventaire::create($request->validated());

            return response()->json([
                'data' => new InfraInventaireResource($infraInventaire),
                'success' => true,
                'message' => 'Inventaire d\'infrastructure créé avec succès',
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(InfraInventaire $infraInventaire): Response
    {
        try {
            $inventaire = InfraInventaire::findOrFail($infraInventaire->id);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $inventaire,
            'success' => true,
            'message' => 'Inventaire d\'infrastructure récupéré avec succès',
        ]);
    }

    public function update(InfraInventaireRequest $request, InfraInventaire $infraInventaire): Response
    {
        try {
            $infraInventaire->update($request->validated());

            return response()->json([
                'data' => new InfraInventaireResource($infraInventaire),
                'success' => true,
                'message' => 'Inventaire d\'infrastructure mis à jour avec succès',
            ]);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(InfraInventaire $infraInventaire): \Illuminate\Http\JsonResponse
    {
        try {
            $infraInventaire->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
