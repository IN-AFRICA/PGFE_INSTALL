<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AmmortissementComptabilityRequest;
use App\Http\Resources\AmmortissementComptabilityResource;
use App\Models\AmmortissementComptability;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class AmmortissementComptabilityController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return AmmortissementComptabilityResource::collection(AmmortissementComptability::latest()->paginate(10));
    }

    public function store(AmmortissementComptabilityRequest $request): AmmortissementComptabilityResource|\Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = $request->user()->id;
            $data['school_id'] = $request->user()->school_id;
            $ammortissementComptability = AmmortissementComptability::create($data);

            return response()->json([
                'data' => new AmmortissementComptabilityResource($ammortissementComptability),
                'message' => 'Ammortissement Comptability cree avec succees.',
            ], Response::HTTP_CREATED
            );
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'message' => $exception->getMessage(),
                'error' => 'Une erreur s\'est produite lors de la creation '], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(AmmortissementComptability $ammortissementComptability): AmmortissementComptabilityResource
    {
        return AmmortissementComptabilityResource::make($ammortissementComptability);
    }

    public function update(AmmortissementComptabilityRequest $request, AmmortissementComptability $ammortissementComptability): AmmortissementComptabilityResource|\Illuminate\Http\JsonResponse
    {
        try {
            $ammortissementComptability->update($request->validated());

            return new AmmortissementComptabilityResource($ammortissementComptability);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(AmmortissementComptability $ammortissementComptability): \Illuminate\Http\JsonResponse
    {
        try {
            $ammortissementComptability->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
