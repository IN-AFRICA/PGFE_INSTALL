<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Students;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentExitRequest;
use App\Models\StudentExit;
use Exception;
use App\Exports\StudentExitsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class StudentExitController extends Controller
{
    /**
     * Export student exits as Excel file.
     */
    public function export(Request $request)
    {
        $fileName = 'student_exits_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new StudentExitsExport(), $fileName);
    }
    public function index(Request $request)
    {
        try {
            $query = StudentExit::query();

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
            if ($request->filled('motif')) {
                $query->where('motif', $request->input('motif'));
            }
            if ($request->filled('type')) {
                $query->where('type', $request->input('type'));
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            $perPage = (int) $request->input('per_page', 20);
            $studentExit = $query->latest()->paginate($perPage);

            return response()->json([
                'data' => $studentExit,
                'success' => true,
                'message' => 'Liste des eleves sorti recupere avec succes',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function store(StudentExitRequest $request)
    {
        try {
            $existingExit = StudentExit::where('student_id', $request->student_id)->first();
            if ($existingExit) {
                return response()->json([
                    'error' => 'Un élève avec cet ID existe déjà.',
                ], Response::HTTP_CONFLICT);
            }
            $exit = StudentExit::create($request->validated());

            return response()->json([
                'data' => $exit,
                'success' => true,
                'message' => 'Sortie d\'élève créée avec succès',
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $studentExit)
    {
        try {
            if (! $studentExit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sortie d\'élève non trouvée.',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'data' => $studentExit,
                'success' => true,
                'message' => 'Sortie d\'élève récupérée avec succès',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la sortie d\'élève: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update(StudentExitRequest $request, int $studentExitId)
    {
        try {
            $studentExit = StudentExit::find($studentExitId);
            if (! $studentExit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sortie d\'élève non trouvée.',
                ], Response::HTTP_NOT_FOUND);
            }
            $studentExit->update($request->validated());

            return response()->json([
                'data' => $studentExit,
                'success' => true,
                'message' => 'Sortie d\'élève mise à jour avec succès',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sortie d\'élève non trouvée.'.$e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }

    }

    public function destroy(StudentExit $studentExit)
    {
        try {
            $studentExit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sortie d\'élève supprimée avec succès',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la sortie d\'élève: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
