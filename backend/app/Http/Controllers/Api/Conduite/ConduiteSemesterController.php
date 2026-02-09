<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Conduite;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConduiteSemesterRequest;
use App\Http\Resources\ConduiteSemesterResource;
use App\Models\ConduiteSemester;
use Exception;
use Illuminate\Http\Response;

final class ConduiteSemesterController extends Controller
{
    public function index()
    {
        $query = ConduiteSemester::query()->with(['conduite', 'schoolYear', 'semester']);

        // Optional filters: school_year_id, semester_id
        if (request()->filled('school_year_id') && is_numeric(request('school_year_id'))) {
            $query->where('school_year_id', (int) request('school_year_id'));
        }
        if (request()->filled('semester_id') && is_numeric(request('semester_id'))) {
            $query->where('semester_id', (int) request('semester_id'));
        }
        if (request()->filled('conduite_id') && is_numeric(request('conduite_id'))) {
            $query->where('conduite_id', (int) request('conduite_id'));
        }

        $items = $query->orderByDesc('id')->get();

        return response()->json([
            'data' => ConduiteSemesterResource::collection($items),
            'message' => 'Liste des semestres de conduite récupérée avec succès',
            'success' => true,
        ]);
    }

    public function store(ConduiteSemesterRequest $request)
    {
        $case = ConduiteSemester::create($request->validated());

        return response()->json([
            'data' => $case,
            'message' => 'Semestre de conduite créé avec succès',
            'success' => true,
        ], Response::HTTP_CREATED);
    }

    public function show(ConduiteSemester $conduiteSemester)
    {
        try {
            $conduiteSemester->load(['schoolYear', 'semester']);

            return response()->json([
                'data' => new ConduiteSemesterResource($conduiteSemester),
                'message' => 'Semestre de conduite récupéré avec succès',
                'success' => true,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }

    }

    public function update(ConduiteSemesterRequest $request, ConduiteSemester $conduiteSemester)
    {

        try {
            $conduiteSemester->update($request->validated());

            return response()->json([
                'data' => $conduiteSemester,
                'message' => 'Semestre de conduite mis à jour avec succès',
                'success' => true,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }

    }

    public function destroy(ConduiteSemester $conduiteSemester)
    {
        $conduiteSemester->delete();

        return response()->json([
            'message' => 'Semestre de conduite supprimé avec succès',
            'success' => true,
        ], Response::HTTP_NO_CONTENT);
    }
}
