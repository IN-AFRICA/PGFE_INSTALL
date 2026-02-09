<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Schools;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolRequest;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

final class SchoolController extends Controller
{
    public function index(): JsonResponse
    {
        $schools = School::with('province')->latest()->get();

        return response()->json([
            'data' => $schools,
            'message' => 'Liste des écoles récupérée avec succès',
        ]);
    }

    public function store(SchoolRequest $request): JsonResponse
    {
        $request->validated();

        $validatedData = $request->safe()->except(['logo']);

        if ($request->hasFile('logo')) {
            $validatedData['logo'] = $request->file('logo')->storePublicly('schools/logos', 'public');
        }
        $school = School::create($validatedData);

        return response()->json([
            'data' => $school->load('province'),
            'message' => 'École créée avec succès',
        ], 201);
    }

    public function show(School $school): JsonResponse
    {
        return response()->json([
            'data' => $school->load(['province', 'classrooms', 'personals', 'visits']),
            'message' => 'École récupérée avec succès',
        ]);
    }

    public function update(SchoolRequest $request, School $school): JsonResponse
    {
        $request->validated();

        $validatedData = $request->safe()->except(['logo']);

        if ($request->hasFile('logo')) {
            if ($school->logo) {
                Storage::disk('public')->delete($school->logo);
            }

            $validatedData['logo'] = $request->file('logo')->storePublicly('schools/logos', 'public');
        }

        $school->update($validatedData);

        return response()->json([
            'data' => $school->fresh(['province']),
            'message' => 'École mise à jour avec succès',
        ]);
    }

    public function destroy(School $school): JsonResponse
    {
        if ($school->logo) {
            Storage::delete($school->logo);
        }
        $school->delete();

        return response()->json([
            'message' => 'École supprimée avec succès',
        ]);
    }
}
