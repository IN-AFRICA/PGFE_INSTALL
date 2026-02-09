<?php

namespace App\Http\Controllers\Api\Schedules;

use App\Http\Controllers\Controller;
use App\Models\AcademicPersonal;
use App\Models\Fonction;
use App\Models\Schedule;
use App\Models\TeacherUnavailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Get list of available teachers for a specific time slot.
     */
    public function availableTeachers(Request $request): JsonResponse
    {
        $request->validate([
            'day' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'week_number' => 'nullable|integer',
        ]);

        $day = $request->input('day');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');
        $weekNumber = $request->input('week_number');
        $schoolId = $request->user()->school_id;

        // 1. Trouver l'ID de la fonction "Enseignant" (ou "Teacher")
        $enseignantFonction = Fonction::where('title', 'like', '%Enseignant%')
            ->orWhere('title', 'like', '%Professeur%')
            ->orWhere('title', 'like', '%Teacher%')
            ->first();

        if (!$enseignantFonction) {
            return response()->json([
                'success' => false,
                'message' => 'Fonction "Enseignant" non trouvée dans le système.',
            ], 404);
        }

        // 2. Récupérer tous les enseignants de l'école
        $teachersQuery = AcademicPersonal::where('school_id', $schoolId)
            ->where('fonction_id', $enseignantFonction->id);

        // 3. Identifier les enseignants déjà occupés par l'horaire
        $busyTeacherIds = Schedule::where('school_id', $schoolId)
            ->where('day', $day)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->when($weekNumber, function ($query) use ($weekNumber) {
                return $query->where(function ($q) use ($weekNumber) {
                    $q->where('week_number', $weekNumber)
                      ->orWhereNull('week_number');
                });
            })
            ->pluck('academic_personal_id')
            ->unique();

        // 4. Identifier les enseignants ayant une indisponibilité déclarée
        $unavailableTeacherIds = TeacherUnavailability::where('day', $day)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->pluck('academic_personal_id')
            ->unique();

        // 5. Exclure les IDs occupés/indisponibles
        $excludedIds = $busyTeacherIds->merge($unavailableTeacherIds)->unique();

        $availableTeachers = $teachersQuery->whereNotIn('id', $excludedIds)->get();

        return response()->json([
            'success' => true,
            'data' => $availableTeachers,
            'message' => 'Liste des enseignants disponibles récupérée avec succès.',
        ]);
    }

    /**
     * Store a new schedule entry.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_year_id' => 'required|exists:school_years,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'academic_personal_id' => 'required|exists:academic_personals,id',
            'course_id' => 'required|exists:courses,id',
            'day' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'week_number' => 'nullable|integer',
        ]);

        $schoolId = $request->user()->school_id;

        // Vérification de dernier recours contre les conflits (enseignant)
        $conflict = Schedule::where('school_id', $schoolId)
            ->where('academic_personal_id', $validated['academic_personal_id'])
            ->where('day', $validated['day'])
            ->where(function ($query) use ($validated) {
                $query->where('start_time', '<', $validated['end_time'])
                      ->where('end_time', '>', $validated['start_time']);
            })
            ->when($validated['week_number'] ?? null, function ($query, $week) {
                return $query->where(function ($q) use ($week) {
                    $q->where('week_number', $week)
                      ->orWhereNull('week_number');
                });
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Cet enseignant est déjà occupé sur ce créneau horaire.',
            ], 422);
        }

        // Vérification conflit salle/classe
        $classConflict = Schedule::where('school_id', $schoolId)
            ->where('classroom_id', $validated['classroom_id'])
            ->where('day', $validated['day'])
            ->where(function ($query) use ($validated) {
                $query->where('start_time', '<', $validated['end_time'])
                      ->where('end_time', '>', $validated['start_time']);
            })
            ->when($validated['week_number'] ?? null, function ($query, $week) {
                return $query->where(function ($q) use ($week) {
                    $q->where('week_number', $week)
                      ->orWhereNull('week_number');
                });
            })
            ->exists();

        if ($classConflict) {
            return response()->json([
                'success' => false,
                'message' => 'Cette classe a déjà un cours prévu sur ce créneau horaire.',
            ], 422);
        }

        $schedule = Schedule::create(array_merge($validated, ['school_id' => $schoolId]));

        return response()->json([
            'success' => true,
            'data' => $schedule,
            'message' => 'Horaire enregistré avec succès.',
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id;
        $query = Schedule::with(['academicPersonal', 'classroom', 'course'])
            ->where('school_id', $schoolId);

        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->input('classroom_id'));
        }

        if ($request->filled('academic_personal_id')) {
            $query->where('academic_personal_id', $request->input('academic_personal_id'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }
}
