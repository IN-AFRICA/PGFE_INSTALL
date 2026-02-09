<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\DisciplinaryActions;

use App\Http\Controllers\Controller;
use App\Http\Requests\AbandonCaseRequest;
use App\Models\AbandonCase;
use Illuminate\Http\Request;

final class AbandonCaseController extends Controller
{
    public function index(Request $request)
    {
        $query = AbandonCase::query();

        // Filtres optionnels
        if ($request->filled('school_id') && is_numeric($request->input('school_id'))) {
            $query->where('school_id', (int) $request->input('school_id'));
        }
        if ($request->filled('classroom_id') && is_numeric($request->input('classroom_id'))) {
            $query->where('classroom_id', (int) $request->input('classroom_id'));
        }
        if ($request->filled('student_id') && is_numeric($request->input('student_id'))) {
            $query->where('student_id', (int) $request->input('student_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        // Plage de dates
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $perPage = (int) $request->input('per_page', 20);
        $cases = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $cases,
            'success' => true,
            'message' => 'Liste des cas d\'abandon',
        ], 200);
    }

    public function store(AbandonCaseRequest $request)
    {
        $data = $request->validated();

        $exists = AbandonCase::query()
            ->where('student_id', $data['student_id'])
            ->where('school_year_id', $data['school_year_id'])
            ->where('classroom_id', $data['classroom_id'])
            ->where('semester_id', $data['semester_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => "Cet élève est déjà marqué en abandon pour cette classe, cette année scolaire et ce semestre.",
            ], 422);
        }

        $case = AbandonCase::create($data);

        return response()->json([
            'data' => $case,
            'success' => true,
            'message' => 'Cas d\'abandon créé avec succès.',
        ], 201);
    }

    public function show(AbandonCase $abandonCase)
    {
        return response()->json([
            'data' => $abandonCase,
            'success' => true,
            'message' => 'Cas d\'abandon récupéré avec succès.',
        ], 200);
    }

    public function update(AbandonCaseRequest $request, AbandonCase $abandonCase)
    {
        $abandonCase->update($request->validated());

        return response()->json([
            'data' => $abandonCase,
            'success' => true,
            'message' => 'Cas d\'abandon mis à jour avec succès.',
        ], 200);
    }

    public function destroy(AbandonCase $abandonCase)
    {
        $abandonCase->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cas d\'abandon supprimé avec succès.',
        ], 204);
    }
}
