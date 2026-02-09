<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Infrastructure;

use App\Http\Controllers\Controller;
use App\Http\Requests\InfraEtatRequest;
use App\Http\Resources\InfraEtatResource;
use App\Models\InfraEtat;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class InfraEtatController extends Controller
{
    public function index(): Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $infraEtat = InfraEtat::all();
        try {
            return response()->json([
                'data' => $infraEtat,
                'success' => true,
                'message' => 'Liste des états d\'infrastructures récupérée avec succès',
            ]);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(InfraEtatRequest $request)
    {
        try {
            $infraEtat = InfraEtat::create($request->validated());

            return response()->json([
                'data' => new InfraEtatResource($infraEtat),
                'success' => true,
                'message' => 'État d\'infrastructure créé avec succès',
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $infraEtat)
    {
        try {
            $etat = InfraEtat::findOrFail($infraEtat);

            return response()->json([
                'data' => $etat,
                'success' => true,
                'message' => 'Détails de l\'état d\'infrastructure récupérés avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'There is an error.'.$exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(InfraEtatRequest $request, int $infraEtat)
    {
        try {
            $etat = InfraEtat::findOrFail($infraEtat);
            $etat->update($request->validated());

            return response()->json([
                'data' => $etat,
                'success' => true,
                'message' => 'État d\'infrastructure mis à jour avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(InfraEtat $infraEtat)
    {
        try {
            $infraEtat->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
