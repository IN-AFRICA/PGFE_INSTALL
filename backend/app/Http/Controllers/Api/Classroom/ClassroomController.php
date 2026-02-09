<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Classroom;

use App\Exports\ClassroomsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClassroomRequest;
use App\Models\Classroom;
use App\Models\Filiaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

final class ClassroomController extends Controller
{
    /**
     * Export classrooms as Excel file.
     */
    public function export(Request $request)
    {
        $fileName = 'classrooms_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new ClassroomsExport(), $fileName);
    }
    public function index(Request $request): JsonResponse
    {
        $query = Classroom::query()
            ->with(['academicLevel.cycle.filiaire', 'titulaire']);

        // Filtres optionnels
        if ($request->filled('academic_level_id') && is_numeric($request->input('academic_level_id'))) {
            $query->where('academic_level_id', (int) $request->input('academic_level_id'));
        }
        if ($request->filled('cycle_id') && is_numeric($request->input('cycle_id'))) {
            $query->whereHas('academicLevel.cycle', function ($q) use ($request) {
                $q->where('id', (int) $request->input('cycle_id'));
            });
        }
        if ($request->filled('filiaire_id') && is_numeric($request->input('filiaire_id'))) {
            $query->whereHas('academicLevel.cycle.filiaire', function ($q) use ($request) {
                $q->where('id', (int) $request->input('filiaire_id'));
            });
        }
        if ($request->filled('school_id') && is_numeric($request->input('school_id'))) {
            $query->whereHas('academicLevel.cycle.filiaire.school', function ($q) use ($request) {
                $q->where('id', (int) $request->input('school_id'));
            });
        }
        if ($search = $request->input('search')) {
            $search = mb_strtolower(mb_trim($search)); // Assurez-vous que mb_trim est disponible ou utilisez trim
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(indicator) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('academicLevel', function ($q2) use ($search) {
                      $q2->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  })
                  ->orWhereHas('academicLevel.cycle', function ($q2) use ($search) {
                      $q2->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  })
                  ->orWhereHas('academicLevel.cycle.filiaire', function ($q2) use ($search) {
                      $q2->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                          ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        // Pagination dynamique
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0) {
            $perPage = 15; // Valeur par défaut si invalide
        }
        
        $classrooms = $query->latest('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des classes récupérée avec succès',
            'data' => $classrooms->items(),
            'meta' => [
                'current_page' => $classrooms->currentPage(),
                'last_page' => $classrooms->lastPage(),
                'per_page' => $classrooms->perPage(),
                'total' => $classrooms->total(),
            ],
            'links' => [
                 'first' => $classrooms->url(1),
                 'last' => $classrooms->url($classrooms->lastPage()),
                 'prev' => $classrooms->previousPageUrl(),
                 'next' => $classrooms->nextPageUrl(),
            ]
        ]);
    }

    public function store(ClassroomRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Vérifier que le niveau académique existe
        $academicLevel = \App\Models\AcademicLevel::query()
            ->where('id', $data['academic_level_id'] ?? null)
            ->first();

        if (! $academicLevel) {
            return response()->json([
                'success' => false,
                'message' => "Le niveau académique sélectionné n'existe pas.",
            ], 422);
        }

        $classroom = Classroom::create($data);

        return response()->json([
            'data' => $classroom->load(['academicLevel.cycle.filiaire', 'titulaire']),
            'message' => 'Classe créée avec succès',
            'success' => true,
        ], 201);
    }

    public function show(Classroom $classroom): JsonResponse
    {
        $classroom->loadMissing(['academicLevel.cycle.filiaire', 'titulaire']);
        return response()->json([
            'data' => $classroom->load(['academicLevel.cycle.filiaire', 'titulaire']),
            'message' => 'Classe récupérée avec succès',
            'success' => true,
        ]);
    }

    public function update(ClassroomRequest $request, Classroom $classroom): JsonResponse
    {
        $data = $request->validated();

        // Si on change de niveau académique, vérifier qu'il existe
        if (array_key_exists('academic_level_id', $data)) {
            $academicLevel = \App\Models\AcademicLevel::query()
                ->where('id', $data['academic_level_id'])
                ->first();

            if (! $academicLevel) {
                return response()->json([
                    'success' => false,
                    'message' => "Le niveau académique sélectionné n'existe pas.",
                ], 422);
            }
        }

        $classroom->update($data);

        return response()->json([
            'data' => $classroom->fresh(['academicLevel.cycle.filiaire', 'titulaire']),
            'message' => 'Classe mise à jour avec succès',
            'success' => true,
        ]);
    }

    public function destroy(Classroom $classroom): JsonResponse
    {
        $classroom->delete();

        return response()->json([
            'message' => 'Classe supprimée avec succès',
            'success' => true,
        ]);
    }
}
