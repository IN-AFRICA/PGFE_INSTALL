<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Filiaires;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFiliaireRequest;
use App\Models\Filiaire;
use Illuminate\Http\JsonResponse;

final class StoreFiliaireController extends Controller
{
    public function __invoke(StoreFiliaireRequest $request): JsonResponse
    {

        $validated = $request->validated();
        // Forcer l'école depuis l'utilisateur connecté
        $user = $request->user();
        $schoolId = $user?->school_id;
        if (!$schoolId) {
            return response()->json([
                'message' => "Impossible de déterminer l'école de l'utilisateur connecté.",
                'success' => false,
            ], 422);
        }
        $validated['school_id'] = $schoolId;

        $filiaire = Filiaire::query()->create($validated);


        return response()->json([
            'data' => $filiaire->load('cycles.academicLevels'),
            'message' => 'Filière créée avec succès',
        ], 201);
    }
}
