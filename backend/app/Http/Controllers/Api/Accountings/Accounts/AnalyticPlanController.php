<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyticPlanRequest;
use App\Http\Resources\AnalyticPlanResource;
use App\Models\AnalyticPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AnalyticPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AnalyticPlan::query();

        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        }

        $plans = $query->get();

        return response()->json([
            'data' => AnalyticPlanResource::collection($plans),
            'message' => 'Liste des plans analytiques récupérée avec succès',
        ]);
    }

    public function store(AnalyticPlanRequest $request): JsonResponse
    {
        $plan = AnalyticPlan::create($request->validated());

        return response()->json([
            'data' => new AnalyticPlanResource($plan),
            'message' => 'Plan analytique créé avec succès',
        ], 201);
    }

    public function show(AnalyticPlan $analyticPlan): JsonResponse
    {
        return response()->json([
            'data' => new AnalyticPlanResource($analyticPlan),
            'message' => 'Plan analytique récupéré avec succès',
        ]);
    }

    public function update(AnalyticPlanRequest $request, AnalyticPlan $analyticPlan): JsonResponse
    {
        $analyticPlan->update($request->validated());

        return response()->json([
            'data' => new AnalyticPlanResource($analyticPlan),
            'message' => 'Plan analytique mis à jour avec succès',
        ]);
    }

    public function destroy(AnalyticPlan $analyticPlan): JsonResponse
    {
        $analyticPlan->delete();

        return response()->json([
            'message' => 'Plan analytique supprimé avec succès',
        ]);
    }
}
