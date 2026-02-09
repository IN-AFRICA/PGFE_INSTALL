<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\ANewComptabilityRequest;
use App\Http\Resources\ANewComptabilityResource;
use App\Models\ANewComptability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ANewComptabilityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ANewComptability::query();

        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(label) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            });
        }

        return response()->json([
            'data' => ANewComptabilityResource::collection($query->get()),
            'message' => 'Liste des écritures comptables récupérée avec succès',
        ]);
    }

    public function store(ANewComptabilityRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['school_id'] = $request->user()->school_id;
        $data['user_id'] = $request->user()->id;

        $entry = ANewComptability::create($data);

        return response()->json([
            'data' => new ANewComptabilityResource($entry),
            'message' => 'Écriture comptable créée avec succès',
        ], 201);
    }

    public function show(ANewComptability $aNewComptability): JsonResponse
    {
        return response()->json([
            'data' => new ANewComptabilityResource($aNewComptability),
            'message' => 'Écriture comptable récupérée avec succès',
        ]);
    }

    public function update(ANewComptabilityRequest $request, ANewComptability $aNewComptability): JsonResponse
    {
        $aNewComptability->update($request->validated());

        return response()->json([
            'data' => new ANewComptabilityResource($aNewComptability),
            'message' => 'Écriture comptable mise à jour avec succès',
        ]);
    }

    public function destroy(ANewComptability $aNewComptability): JsonResponse
    {
        $aNewComptability->delete();

        return response()->json([
            'message' => 'Écriture comptable supprimée avec succès',
        ]);
    }
}
