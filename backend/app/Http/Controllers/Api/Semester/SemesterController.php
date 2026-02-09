<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Semester;

use App\Http\Controllers\Controller;
use App\Http\Requests\SemesterRequest;
use App\Models\Semester;

final class SemesterController extends Controller
{
    public function index()
    {
        $semester = Semester::all();

        return response()->json([
            'data' => $semester,
            'success' => true,
            'message' => 'Liste des Semestres',
        ], 200);
    }

    public function store(SemesterRequest $request)
    {

        $semester = Semester::create($request->validated());

        return response()->json([
            'data' => $semester,
            'success' => true,
            'message' => 'Semestre créé avec succès.',
        ], 201);
    }

    public function show(Semester $semester)
    {
        return response()->json([
            'data' => $semester,
            'success' => true,
            'message' => 'Semestre récupéré avec succès.',
        ], 200);
    }

    public function update(SemesterRequest $request, Semester $semester)
    {
        $semester->update($request->validated());

        return response()->json([
            'data' => $semester,
            'success' => true,
            'message' => 'Semestre mis à jour avec succès.',
        ], 200);
    }

    public function destroy(Semester $semester)
    {
        $semester->delete();

        return response()->json([
            'success' => true,
            'message' => 'Semestre supprimé avec succès.',
        ], 204);
    }
}
