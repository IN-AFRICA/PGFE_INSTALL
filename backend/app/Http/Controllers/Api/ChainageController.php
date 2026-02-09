<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\Classroom;
use App\Models\Filiaire;
use Illuminate\Http\JsonResponse;

final class ChainageController extends Controller
{
    /**
     * Chaînage: school -> filiaires -> cycle -> academic_level -> classroom
     */

    /**
     * Retourne les filières d'une école donnée
     */
    public function filiairesBySchool(int $schoolId): JsonResponse
    {
        // Now filiaire has a direct school_id
        $filiaires = Filiaire::where('school_id', $schoolId)->get();
        return response()->json(['data' => $filiaires, 'success' => true]);
    }

    /**
     * Retourne le cycle d'une filière donnée
     */
    public function cycleByFiliaire(int $filiaireId): JsonResponse
    {
        // Get all cycles for the filiaire
        $filiaire = Filiaire::with('cycles')->find($filiaireId);
        $cycles = $filiaire?->cycles;
        return response()->json(['data' => $cycles, 'success' => true]);
    }

    /**
     * Retourne les niveaux académiques d'un cycle donné
     */
    public function academicLevelsByCycle(int $cycleId): JsonResponse
    {
        $levels = AcademicLevel::where('cycle_id', $cycleId)->get();
        return response()->json(['data' => $levels, 'success' => true]);
    }

    /**
     * Retourne les filières d'un niveau académique donné
     */
    public function filiairesByAcademicLevel(int $levelId): JsonResponse
    {
        // This no longer makes sense in the new chainage, so return filiaires that have cycles with this academic level
        $filiaires = Filiaire::whereHas('cycles.academicLevels', function ($q) use ($levelId) {
            $q->where('id', $levelId);
        })->get();
        return response()->json(['data' => $filiaires, 'success' => true]);
    }

    /**
     * Retourne les classes d'une filière donnée
     */
    public function classroomsByFiliaire(int $filiaireId): JsonResponse
    {
        $classrooms = Classroom::where('filiaire_id', $filiaireId)->get();
        return response()->json(['data' => $classrooms, 'success' => true]);
    }
        /**
     * Retourne les cycles d'une filière donnée
     */
    public function cyclesByFiliaire(int $filiaireId): JsonResponse
    {
        $cycles = \App\Models\Cycle::where('filiaire_id', $filiaireId)->get();
        return response()->json(['data' => $cycles, 'success' => true]);
    }

    /**
     * Retourne les classes d'un niveau académique donné
     */
    public function classroomsByAcademicLevel(int $academicLevelId): JsonResponse
    {
        $classrooms = \App\Models\Classroom::where('academic_level_id', $academicLevelId)->get();
        return response()->json(['data' => $classrooms, 'success' => true]);
    }
}
