<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mois;

use App\Http\Controllers\Controller;
use App\Http\Requests\MoisRequest;
use App\Http\Resources\MoisResource;
use App\Models\Mois;
use App\Services\MoisService;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class MoisController extends Controller
{
    protected MoisService $moisService;

    /**
     * DummyModel Constructor
     */
    public function __construct(MoisService $moisService)
    {
        $this->moisService = $moisService;
    }

    public function index()
    {
        try {
            return response()->json([
                'data' => Mois::all(),
                'success' => true,
                'message' => 'Liste des mois',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function store(MoisRequest $request): MoisResource|\Illuminate\Http\JsonResponse
    {
        try {
            return response()->json([
                'data' => $this->moisService->save($request->validated()),
                'success' => true,
                'message' => 'Mois créé avec succès.',
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): ?\Illuminate\Http\JsonResponse
    {
        try {
            return response()->json([
                'data' => Mois::findOrFail($id),
                'success' => true,
                'message' => 'Mois récupéré avec succès.',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            return response()->json(['error' => 'Aucun mois trouvé.'], Response::HTTP_NOT_FOUND);
        }

    }

    public function update(MoisRequest $request, int $id)
    {
        try {
            $mois = $this->moisService->getById($id);
            if (! $mois) {
                return response()->json(['error' => 'Aucun mois trouvé.'], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'data' => $this->moisService->update($request->validated(), $id),
                'success' => true,
                'message' => 'Mois mis à jour avec succès.',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'Echec de la mise à jour.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->moisService->deleteById($id);

            return response()->json(['message' => 'Suppression réussie'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'Echec de la suppression.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
