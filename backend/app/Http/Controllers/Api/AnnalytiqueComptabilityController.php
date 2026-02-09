<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnnalytiqueComptabilityRequest;
use App\Http\Resources\AnnalytiqueComptabilityResource;
use App\Models\AnnalytiqueComptability;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class AnnalytiqueComptabilityController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $paginator = AnnalytiqueComptability::latest()->paginate(10);

        return AnnalytiqueComptabilityResource::collection($paginator)
            ->additional(['message' => 'Listes analytiques recuperer avec succees .']);
    }

    public function store(AnnalytiqueComptabilityRequest $request): AnnalytiqueComptabilityResource|\Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = $request->user()->id;
            $data['school_id'] = $request->user()->school_id;
            $annalytiqueComptability = AnnalytiqueComptability::create($data);

            return response()->json([
                'data' => new AnnalytiqueComptabilityResource($annalytiqueComptability),
                'message' => 'Annalytique Comptability cree avec succees.',
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'message' => 'Erreur lors de la creation ',
                'error' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(AnnalytiqueComptability $annalytiqueComptability): AnnalytiqueComptabilityResource
    {
        return AnnalytiqueComptabilityResource::make($annalytiqueComptability);
    }

    public function update(AnnalytiqueComptabilityRequest $request, AnnalytiqueComptability $annalytiqueComptability): AnnalytiqueComptabilityResource|\Illuminate\Http\JsonResponse
    {
        try {
            $annalytiqueComptability->update($request->validated());

            return new AnnalytiqueComptabilityResource($annalytiqueComptability);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(AnnalytiqueComptability $annalytiqueComptability): \Illuminate\Http\JsonResponse
    {
        try {
            $annalytiqueComptability->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
