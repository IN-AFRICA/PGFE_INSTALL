<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImmoAmmortissemenComptabilityRequest;
use App\Http\Resources\ImmoAmmortissemenComptabilityResource;
use App\Models\ImmoAmmortissemenComptability;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ImmoAmmortissemenComptabilityController extends Controller
{
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $query = ImmoAmmortissemenComptability::query();

        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        }

        return ImmoAmmortissemenComptabilityResource::collection($query->latest()->paginate(10));
    }

    public function store(ImmoAmmortissemenComptabilityRequest $request): ImmoAmmortissemenComptabilityResource|\Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = $request->user()->id;
            $data['school_id'] = $request->user()->school_id;

            $immoAmmortissemenComptability = ImmoAmmortissemenComptability::create($data);

            return \response()->json([
                'data' => new ImmoAmmortissemenComptabilityResource($immoAmmortissemenComptability),
                'message' => 'Immo Ammortissemen Comptability cree avec succees.',
            ], Response::HTTP_CREATED
            );
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'There is an error.',
                'message' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(ImmoAmmortissemenComptability $immoAmmortissemenComptability): ImmoAmmortissemenComptabilityResource
    {
        return ImmoAmmortissemenComptabilityResource::make($immoAmmortissemenComptability);
    }

    public function update(ImmoAmmortissemenComptabilityRequest $request, ImmoAmmortissemenComptability $immoAmmortissemenComptability): ImmoAmmortissemenComptabilityResource|\Illuminate\Http\JsonResponse
    {
        try {
            $immoAmmortissemenComptability->update($request->validated());

            return new ImmoAmmortissemenComptabilityResource($immoAmmortissemenComptability);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(ImmoAmmortissemenComptability $immoAmmortissemenComptability): \Illuminate\Http\JsonResponse
    {
        try {
            $immoAmmortissemenComptability->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
