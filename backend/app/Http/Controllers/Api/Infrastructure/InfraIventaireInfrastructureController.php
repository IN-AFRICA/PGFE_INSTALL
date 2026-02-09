<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Infrastructure;

use App\Http\Controllers\Controller;
use App\Http\Requests\InfraIventaireInfrastructureRequest;
use App\Http\Resources\InfraIventaireInfrastructureResource;
use App\Models\InfraIventaireInfrastructure;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class InfraIventaireInfrastructureController extends Controller
{
    public function index()
    {
        try {
            $infrainventaire = InfraIventaireInfrastructure::all();

            return response()->json([
                'data' => $infrainventaire,
                'success' => true,
                'message' => 'Liste des infrastructure recupere avec succees ',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recuperation des infrastructure'.$e->getMessage(),
            ]);
        }
    }

    public function store(InfraIventaireInfrastructureRequest $request)
    {
        try {
            $infraIventaireInfrastructure = InfraIventaireInfrastructure::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'L\'état de l\'infrastructure a été créé avec succès.',
            ]);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'une erreur est survenue.'.$exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $infraIventaireInfrastructure)
    {
        try {
            if (! $infraIventaireInfrastructure) {
                return response()->json([
                    'message' => 'Aucune Infrastructure trouver avec cet id',
                ]);
            }

            return response()->json([
                'message' => 'Infrastructure trouvée avec succès',
                'success' => true,
                'data' => InfraIventaireInfrastructure::find($infraIventaireInfrastructure),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors la recuperation de cet infrastructure '.$e->getMessage(),
            ]);
        }
    }

    public function update(InfraIventaireInfrastructureRequest $request, InfraIventaireInfrastructure $infraIventaireInfrastructure): InfraIventaireInfrastructureResource|\Illuminate\Http\JsonResponse
    {
        try {
            $infraIventaireInfrastructure->update($request->validated());

            return response()->json([
                'message' => 'Mis a jour avec succees',
                'success' => true,
            ]);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(InfraIventaireInfrastructure $infraIventaireInfrastructure): \Illuminate\Http\JsonResponse
    {
        try {
            $infraIventaireInfrastructure->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
