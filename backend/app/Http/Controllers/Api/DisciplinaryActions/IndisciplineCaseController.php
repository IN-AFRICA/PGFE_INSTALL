<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\DisciplinaryActions;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndisciplineCaseRequest;
use App\Models\IndisciplineCase;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class IndisciplineCaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = IndisciplineCase::query();

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
        if ($request->filled('school_year_id') && is_numeric($request->input('school_year_id'))) {
            $query->where('school_year_id', (int) $request->input('school_year_id'));
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->input('date_to'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                // Search by student identity
                $q->whereHas('student', function ($qs) use ($search) {
                    $qs->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(lastname) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(firstname) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(matricule) LIKE ?', ["%{$search}%"]);
                })
                // Or by classroom name
                ->orWhereHas('classroom', function ($qc) use ($search) {
                    $qc->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                })
                // Or by filiere name/code
                ->orWhereHas('filiaire', function ($qf) use ($search) {
                    $qf->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                })
                // Or by action text
                ->orWhereRaw('LOWER(action) LIKE ?', ["%{$search}%"]);
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $cases = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $cases,
            'success' => true,
            'message' => 'Liste des cas d\'indiscipline récupérée avec succès',
        ], Response::HTTP_OK);
    }

    public function store(IndisciplineCaseRequest $request): JsonResponse
    {
        $case = IndisciplineCase::create($request->validated());

        return response()->json([
            'data' => $case,
            'success' => true,
            'message' => 'Cas d\'indiscipline créé avec succès.',
        ], Response::HTTP_CREATED);
    }

    public function show(IndisciplineCase $indisciplineCase): JsonResponse
    {
        try {
            if (! $indisciplineCase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cas d\'indiscipline non trouvé.',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'data' => $indisciplineCase,
                'success' => true,
                'message' => 'Cas d\'indiscipline récupéré avec succès.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du cas d\'indiscipline: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update(IndisciplineCaseRequest $request, IndisciplineCase $indisciplineCase): JsonResponse
    {
        try {
            $indisciplineCase->update($request->validated());

            return response()->json([
                'data' => $indisciplineCase,
                'success' => true,
                'message' => 'Cas d\'indiscipline mis à jour avec succès.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du cas d\'indiscipline: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function destroy(IndisciplineCase $indisciplineCase)
    {
        try {
            $indisciplineCase->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cas d\'indiscipline supprimé avec succès.',
            ], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du cas d\'indiscipline: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
