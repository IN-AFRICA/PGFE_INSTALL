<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Infrastructure;

use App\Http\Controllers\Controller;
use App\Http\Requests\InfraEquipementRequest;
use App\Http\Resources\InfraEquipementResource;
use App\Models\InfraEquipement;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class InfraEquipementController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return InfraEquipementResource::collection(InfraEquipement::latest()->paginate(10));
    }

    public function store(InfraEquipementRequest $request): InfraEquipementResource|\Illuminate\Http\JsonResponse
    {
        try {
            $infraEquipement = InfraEquipement::create($request->validated());

            return new InfraEquipementResource($infraEquipement);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(InfraEquipement $infraEquipement): InfraEquipementResource
    {
        return InfraEquipementResource::make($infraEquipement);
    }

    public function update(InfraEquipementRequest $request, InfraEquipement $infraEquipement): InfraEquipementResource|\Illuminate\Http\JsonResponse
    {
        try {
            $infraEquipement->update($request->validated());

            return new InfraEquipementResource($infraEquipement);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(InfraEquipement $infraEquipement): \Illuminate\Http\JsonResponse
    {
        try {
            $infraEquipement->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
