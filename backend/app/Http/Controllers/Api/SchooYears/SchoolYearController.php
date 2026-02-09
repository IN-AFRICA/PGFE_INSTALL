<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchooYears;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SchoolYearController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->query('school_id');

        $years = SchoolYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des années scolaires',
            'years' => $years,
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        // Prévention double activation
        if (! empty($data['is_active']) && $data['is_active']) {
            SchoolYear::where('school_id', $data['school_id'])
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $year = SchoolYear::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Année scolaire créée avec succès',
            'year' => $year,
        ], 201);
    }

    public function show(SchoolYear $schoolYear)
    {
        return response()->json([
            'success' => true,
            'message' => 'Détails de l\'année scolaire',
            'year' => $schoolYear,
        ], 200);
    }

    public function update(Request $request, SchoolYear $schoolYear)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        if (! empty($data['is_active']) && $data['is_active']) {
            SchoolYear::where('school_id', $schoolYear->school_id)
                ->where('id', '!=', $schoolYear->id)
                ->update(['is_active' => false]);
        }

        $schoolYear->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Année scolaire mise à jour avec succès',
            'year' => $schoolYear,
        ], 200);
    }

    public function destroy(SchoolYear $schoolYear)
    {
        $schoolYear->delete();

        return response()->json([
            'success' => true,
            'message' => 'Année scolaire supprimée avec succès',
        ], 200);
    }

    public function activate(SchoolYear $schoolYear)
    {
        // On désactive toutes les autres années
        SchoolYear::where('school_id', $schoolYear->school_id)
            ->where('id', '!=', $schoolYear->id)
            ->update(['is_active' => false]);

        // On active celle-ci
        $schoolYear->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => "Année scolaire {$schoolYear->name} activée",
            'year' => $schoolYear->fresh(),
        ], 200);
    }

    public function active(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non rattaché à une école',
                'year' => null,
            ], 422);
        }

        $refresh = filter_var($request->query('refresh', false), FILTER_VALIDATE_BOOLEAN);
        // Cast explicite pour éviter l'erreur "string given"
        $year = SchoolYear::active((int) $user->school_id, $refresh);

        if (! $year) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune année scolaire active pour cette école',
                'year' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Année scolaire active récupérée avec succès',
            'year' => $year,
        ]);
    }
}
