<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Person;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonListeSalariesRequest;
use App\Http\Resources\PersonListeSalariesResource;
use App\Models\PersonListeSalaries;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class PersonListeSalariesController extends Controller
{
    public function index()
    {
        try {
            if (PersonListeSalaries::all()->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'success' => true,
                    'message' => 'Aucune des Liste Salaires trouvé.',
                ], Response::HTTP_OK);
            }
        } catch (Exception $exception) {
            report($exception);
        }

        return response()->json([
            'data' => PersonListeSalaries::all(),
            'success' => true,
            'message' => 'Liste des Liste Salaires',
        ], Response::HTTP_OK);
    }

    public function store(PersonListeSalariesRequest $request): PersonListeSalariesResource|\Illuminate\Http\JsonResponse
    {
        try {
            $personListeSalaries = PersonListeSalaries::create($request->validated());

            return response()->json([
                'data' => $personListeSalaries,
                'success' => true,
                'message' => 'Liste Salaire créé avec succès.',
            ], Response::HTTP_CREATED);

        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(PersonListeSalaries $personListeSalaries)
    {
        try {
            if (! $personListeSalaries) {
                return response()->json([
                    'data' => null,
                    'success' => false,
                    'message' => 'Liste Salaire non trouvé.',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'data' => $personListeSalaries::findOrFail($personListeSalaries->id),
                'success' => true,
                'message' => 'Liste Salaire trouvé.',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);
        }
    }

    public function update(PersonListeSalariesRequest $request, PersonListeSalaries $personListeSalaries): PersonListeSalariesResource|\Illuminate\Http\JsonResponse
    {
        try {
            $personListeSalaries->update($request->validated());

            return response()->json([
                'data' => $personListeSalaries::findOrFail($personListeSalaries->id),
                'success' => true,
                'message' => 'Liste Salaire mis à jour avec succès.',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['message' => 'Erreur lors de la mise à jour.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(PersonListeSalaries $personListeSalaries): \Illuminate\Http\JsonResponse
    {
        try {
            $personListeSalaries->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
