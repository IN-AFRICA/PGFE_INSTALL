<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Infrastructure;

use App\Http\Controllers\Controller;
use App\Http\Requests\InfraInfrastructureRequest;
use App\Models\InfraInfrastructure;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class InfraInfrastructureController extends Controller
{
    public function index(): Response
    {
        try {
            return response()->json([
                'data' => InfraInfrastructure::all(),
                'success' => true,
                'message' => 'Liste des infrastructures',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'Une erreur est survenue.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(InfraInfrastructureRequest $request)
    {
        try {
            $infraInfrastructure = InfraInfrastructure::create($request->validated());

            return response()->json([
                'data' => $infraInfrastructure,
                'success' => true,
                'message' => 'Infrastructure créée avec succès',
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'Une erreur est survenue.'.$exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id)
    {
        try {
            $infraInfrastructure = InfraInfrastructure::find($id);
            if (! $infraInfrastructure) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune infrastructure trouvée avec cet ID'], Response::HTTP_NOT_FOUND);
            }
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $infraInfrastructure,
            'success' => true,
            'message' => 'Infrastructure récupérée avec succès',
        ], Response::HTTP_OK);
    }

    public function update(InfraInfrastructureRequest $request, int $id)
    {
        try {
            $infraInfrastructure = InfraInfrastructure::find($id);
            if (! $infraInfrastructure) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune infrastructure trouvée avec cet ID'], Response::HTTP_NOT_FOUND);
            }
            $infraInfrastructure->update($request->validated());

            return response()->json([
                'data' => $infraInfrastructure,
                'success' => true,
                'message' => 'Infrastructure mise à jour avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue.'.$exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id)
    {
        try {
            $infraInfrastructure = InfraInfrastructure::find($id);
            if (! $infraInfrastructure) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune infrastructure trouvée avec cet ID'], Response::HTTP_NOT_FOUND);
            }
            $infraInfrastructure->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
