<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Infrastructure;

use App\Http\Controllers\Controller;
use App\Http\Requests\InfraCategorieRequest;
use App\Http\Resources\InfraCategorieResource;
use App\Models\InfraCategorie;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class InfraCategorieController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection|Response
    {
        return response()->json([
            'data' => InfraCategorieResource::collection(InfraCategorie::all()),
            'success' => true,
            'message' => 'Liste des catégories d\'infrastructures récupérée avec succès',
        ]);
    }

    public function store(InfraCategorieRequest $request): InfraCategorieResource|\Illuminate\Http\JsonResponse
    {
        try {
            $infraCategorie = InfraCategorie::create($request->validated());

            return new InfraCategorieResource($infraCategorie);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(InfraCategorie $infraCategorie): InfraCategorieResource
    {
        return InfraCategorieResource::make($infraCategorie);
    }

    public function update(InfraCategorieRequest $request, InfraCategorie $infraCategorie): InfraCategorieResource|\Illuminate\Http\JsonResponse
    {
        try {
            $infraCategorie->update($request->validated());

            return new InfraCategorieResource($infraCategorie);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(InfraCategorie $infraCategorie): \Illuminate\Http\JsonResponse
    {
        try {
            $infraCategorie->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
