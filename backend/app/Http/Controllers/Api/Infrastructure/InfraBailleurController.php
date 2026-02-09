<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Infrastructure;

use App\Http\Controllers\Controller;
use App\Http\Requests\InfraBailleurRequest;
use App\Http\Resources\InfraBailleurResource;
use App\Models\InfraBailleur;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class InfraBailleurController extends Controller
{
    public function index(): Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return response()->json([
            'data' => InfraBailleurResource::collection(InfraBailleur::latest()->paginate(10)),
            'success' => true,
            'message' => 'Liste des bailleurs d\'infrastructures récupérée avec succès',
        ]);
    }

    public function store(InfraBailleurRequest $request): InfraBailleurResource|\Illuminate\Http\JsonResponse|Response
    {
        try {
            $infraBailleur = InfraBailleur::create($request->validated());

            return response()->json([
                'data' => new InfraBailleurResource($infraBailleur),
                'success' => true,
                'message' => 'Bailleur d\'infrastructure créé avec succès',
            ], Response::HTTP_CREATED);
            // return new InfraBailleurResource($infraBailleur);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(InfraBailleur $infraBailleur): InfraBailleurResource|Response
    {
        try {
            $bailleur = InfraBailleur::findOrFail($infraBailleur->id);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $bailleur,
            'success' => true,
            'message' => 'Détails du bailleur d\'infrastructure récupérés avec succès',
        ], Response::HTTP_OK);
        // return InfraBailleurResource::make($infraBailleur);
    }

    public function update(InfraBailleurRequest $request, InfraBailleur $infraBailleur): InfraBailleurResource|\Illuminate\Http\JsonResponse|Response
    {
        try {
            $infraBailleur->update($request->validated());

            return response()->json([
                'data' => new InfraBailleurResource($infraBailleur),
                'success' => true,
                'message' => 'Bailleur d\'infrastructure mis à jour avec succès',
            ]);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(InfraBailleur $infraBailleur): \Illuminate\Http\JsonResponse|Response
    {
        try {
            $infraBailleur->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
