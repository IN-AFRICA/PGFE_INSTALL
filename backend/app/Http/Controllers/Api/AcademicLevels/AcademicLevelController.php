<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\AcademicLevels;

use App\Exports\AcademicLevelsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicLevelRequest;
use App\Models\AcademicLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

final class AcademicLevelController extends Controller
{
    /**
     * Export academic levels as Excel file.
     */
    public function export(Request $request)
    {
        $fileName = 'academic_levels_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new AcademicLevelsExport(), $fileName);
    }
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $query = AcademicLevel::with(['cycle', 'classrooms'])->latest();

        // Le scope global ScopeBySchool filtre déjà par école via cycle.filiaire.school_id

        // Nouveau: filtre par cycle
        if ($request->filled('cycle_id')) {
            $query->where('cycle_id', (int) $request->input('cycle_id'));
        }

        // Recherche filtrante sur le nom du niveau académique et du cycle
        if ($request->filled('search')) {
            $search = mb_strtolower(mb_trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('cycle', function ($q2) use ($search) {
                      $q2->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        $academicLevels = $query->get();

        return response()->json([
            'data' => $academicLevels,
            'message' => 'Liste des niveaux académiques récupérée avec succès',
        ]);
    }

    public function store(AcademicLevelRequest $request): JsonResponse
    {
        $academicLevel = AcademicLevel::create($request->validated());

        return response()->json([
            'data' => $academicLevel->load('cycle'),
            'message' => 'Niveau académique créé avec succès',
        ], 201);
    }

    public function update(AcademicLevelRequest $request, AcademicLevel $academicLevel): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $academicLevel->load('classrooms');
        $userSchoolId = $user?->school_id;
        $belongsToSchool = false;
        if ($userSchoolId) {
            $belongsToSchool = $academicLevel->classrooms->contains(function ($classroom) use ($userSchoolId) {
                return (int) ($classroom->school_id ?? 0) === (int) $userSchoolId;
            });
        }
        if ($userSchoolId && ! $belongsToSchool) {
            return response()->json([
                'success' => false,
                'message' => "Accès refusé: ce niveau n'appartient pas à votre école.",
            ], 403);
        }

        $academicLevel->update($request->validated());

        return response()->json([
            'data' => $academicLevel->fresh('cycle'),
            'message' => 'Niveau académique mis à jour avec succès',
        ]);
    }

    public function destroy(AcademicLevel $academicLevel): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $academicLevel->load('classrooms');
        $userSchoolId = $user?->school_id;
        $belongsToSchool = false;
        if ($userSchoolId) {
            $belongsToSchool = $academicLevel->classrooms->contains(function ($classroom) use ($userSchoolId) {
                return (int) ($classroom->school_id ?? 0) === (int) $userSchoolId;
            });
        }
        if ($userSchoolId && ! $belongsToSchool) {
            return response()->json([
                'success' => false,
                'message' => "Accès refusé: ce niveau n'appartient pas à votre école.",
            ], 403);
        }

        $academicLevel->delete();

        return response()->json([
            'message' => 'Niveau académique supprimé avec succès',
        ]);
    }
}
