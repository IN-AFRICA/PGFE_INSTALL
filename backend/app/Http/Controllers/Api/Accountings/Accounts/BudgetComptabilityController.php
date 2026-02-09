<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetComptabilityRequest;
use App\Http\Resources\BudgetComptabilityResource;
use App\Models\BudgetComptability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BudgetComptabilityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BudgetComptability::query();

        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            });
        }

        return response()->json([
            'data' => BudgetComptabilityResource::collection($query->get()),
            'message' => 'Liste des budgets récupérée avec succès',
        ]);
    }

    public function store(BudgetComptabilityRequest $request): JsonResponse
    {
        $budget = BudgetComptability::create($request->validated());

        return response()->json([
            'data' => new BudgetComptabilityResource($budget),
            'message' => 'Budget créé avec succès',
        ], 201);
    }

    public function show(BudgetComptability $budgetComptability): JsonResponse
    {
        return response()->json([
            'data' => new BudgetComptabilityResource($budgetComptability),
            'message' => 'Budget récupéré avec succès',
        ]);
    }

    public function update(BudgetComptabilityRequest $request, BudgetComptability $budgetComptability): JsonResponse
    {
        $budgetComptability->update($request->validated());

        return response()->json([
            'data' => new BudgetComptabilityResource($budgetComptability),
            'message' => 'Budget mis à jour avec succès',
        ]);
    }

    public function destroy(BudgetComptability $budgetComptability): JsonResponse
    {
        $budgetComptability->delete();

        return response()->json([
            'message' => 'Budget supprimé avec succès',
        ]);
    }
}
