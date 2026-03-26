<?php

namespace App\Http\Controllers\Api\Schedules;

use App\Http\Controllers\Controller;
use App\Models\AcademicPersonal;
use App\Models\Fonction;
use App\Models\Schedule;
use App\Models\SchoolYear;
use App\Models\TeacherUnavailability;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller{

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
        \Log::info('calendar/weeks params', $request->all());

        \DB::listen(function ($query) {
            \Log::info($query->sql, $query->bindings);
        });

        $validated = $request->validate([
            'school_year_id' => [
                'required',
                Rule::exists('school_years', 'id')->where('is_active', 1),
            ],
            'classroom_id' => [
                'required',
                Rule::exists('classrooms', 'id')->where('school_id', $request->user()->school_id),
            ],
            'academic_personal_id' => [
                'required',
                Rule::exists('academic_personals', 'id')->where('school_id', $request->user()->school_id),
            ],
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')->where('school_id', $request->user()->school_id),
            ],
            'day' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'week_number' => 'nullable|integer',
        ]);

        $year = SchoolYear::where('id', $request->school_year_id)
            ->where('is_active', 1)
            ->first();
        \Log::info('SchoolYear found', ['year' => $year]);

        if (!$year) {
            \Log::warning('Invalid school_year_id', ['id' => $request->school_year_id]);
        }

        // Vérification de dernier recours contre les conflits (enseignant)
        $conflict = Schedule::where('school_id', $request->user()->school_id)
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
        $classConflict = Schedule::where('school_id', $request->user()->school_id)
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


        // Ajout UUID automatique si non fourni (sécurité côté contrôleur)
        $data = array_merge($validated, ['school_id' => $request->user()->school_id]);
        if (empty($data['uuid'])) {
            $data['uuid'] = (string) \Illuminate\Support\Str::uuid();
        }
        $schedule = Schedule::create($data);

        return response()->json([
            'success' => true,
            'data' => $schedule,
            'message' => 'Horaire enregistré avec succès.',
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id;

        $validated = $request->validate([
            'school_year_id' => [
                'sometimes',
                'required',
                Rule::exists('school_years', 'id')->where(function ($query) use ($schoolId) {
                    $query->where('school_id', $schoolId)->orWhereNull('school_id');
                }),
            ],
            // month n'est pas stocké côté Schedule aujourd'hui; on le valide juste pour éviter valeurs bizarres
            'month' => ['sometimes', 'required', 'integer', 'between:1,12'],
            'week_number' => ['sometimes', 'required', 'integer', 'between:1,53'],
            'classroom_id' => [
                'sometimes',
                'required',
                Rule::exists('classrooms', 'id')->where('school_id', $schoolId),
            ],
            'academic_personal_id' => [
                'sometimes',
                'required',
                Rule::exists('academic_personals', 'id')->where('school_id', $schoolId),
            ],
        ]);

        $query = Schedule::with(['academicPersonal', 'classroom', 'course'])
            ->where('school_id', $schoolId);

        if (Arr::has($validated, 'school_year_id')) {
            $query->where('school_year_id', $validated['school_year_id']);
        }

        if (Arr::has($validated, 'week_number')) {
            $weekNumber = (int) $validated['week_number'];
            // Inclure les cours récurrents (week_number = null)
            $query->where(function ($q) use ($weekNumber) {
                $q->whereNull('week_number')
                    ->orWhere('week_number', $weekNumber);
            });
        }

        // Enseignant: lecture uniquement de son propre horaire
        if ($request->user()->hasRole('enseignant') && !$request->user()->hasAnyRole(['admin-ecole', 'super-admin'])) {
            $academicPersonalId = $request->user()->academicPersonal?->id;
            if (!$academicPersonalId) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                ]);
            }

            $query->where('academic_personal_id', $academicPersonalId);
        }

        if (Arr::has($validated, 'classroom_id')) {
            $query->where('classroom_id', $validated['classroom_id']);
        }

        if (Arr::has($validated, 'academic_personal_id')) {
            $query->where('academic_personal_id', $validated['academic_personal_id']);
        }

        // Ordre stable pour l'affichage
        $query->orderBy('day')->orderBy('start_time');

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }
    /**
     * Update an existing schedule entry.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $schoolId = $request->user()->school_id;

        $validated = $request->validate([
            'school_year_id' => [
                'sometimes',
                'required',
                Rule::exists('school_years', 'id')->where('school_id', $schoolId),
            ],
            'classroom_id' => [
                'sometimes',
                'required',
                Rule::exists('classrooms', 'id')->where('school_id', $schoolId),
            ],
            'academic_personal_id' => [
                'sometimes',
                'required',
                Rule::exists('academic_personals', 'id')->where('school_id', $schoolId),
            ],
            'course_id' => [
                'sometimes',
                'required',
                Rule::exists('courses', 'id')->where('school_id', $schoolId),
            ],
            'day' => 'sometimes|required|string',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i',
            'week_number' => 'nullable|integer',
        ]);
        $schedule = Schedule::where('school_id', $schoolId)->findOrFail($id);

        $effective = [
            'school_year_id' => $validated['school_year_id'] ?? $schedule->school_year_id,
            'classroom_id' => $validated['classroom_id'] ?? $schedule->classroom_id,
            'academic_personal_id' => $validated['academic_personal_id'] ?? $schedule->academic_personal_id,
            'course_id' => $validated['course_id'] ?? $schedule->course_id,
            'day' => $validated['day'] ?? $schedule->day,
            'start_time' => $validated['start_time'] ?? $schedule->start_time,
            'end_time' => $validated['end_time'] ?? $schedule->end_time,
            'week_number' => array_key_exists('week_number', $validated) ? $validated['week_number'] : $schedule->week_number,
        ];

        if (strtotime((string) $effective['start_time']) >= strtotime((string) $effective['end_time'])) {
            return response()->json([
                'success' => false,
                'message' => 'L\'heure de fin doit être après l\'heure de début.',
            ], 422);
        }

        // Vérification de dernier recours contre les conflits (enseignant)
        $teacherConflict = Schedule::where('school_id', $schoolId)
            ->where('id', '!=', $schedule->id)
            ->where('academic_personal_id', $effective['academic_personal_id'])
            ->where('day', $effective['day'])
            ->where(function ($query) use ($effective) {
                $query->where('start_time', '<', $effective['end_time'])
                    ->where('end_time', '>', $effective['start_time']);
            })
            ->when(!is_null($effective['week_number']), function ($query) use ($effective) {
                return $query->where(function ($q) use ($effective) {
                    $q->where('week_number', $effective['week_number'])
                        ->orWhereNull('week_number');
                });
            })
            ->exists();

        if ($teacherConflict) {
            return response()->json([
                'success' => false,
                'message' => 'Cet enseignant est déjà occupé sur ce créneau horaire.',
            ], 422);
        }

        // Vérification conflit salle/classe
        $classConflict = Schedule::where('school_id', $schoolId)
            ->where('id', '!=', $schedule->id)
            ->where('classroom_id', $effective['classroom_id'])
            ->where('day', $effective['day'])
            ->where(function ($query) use ($effective) {
                $query->where('start_time', '<', $effective['end_time'])
                    ->where('end_time', '>', $effective['start_time']);
            })
            ->when(!is_null($effective['week_number']), function ($query) use ($effective) {
                return $query->where(function ($q) use ($effective) {
                    $q->where('week_number', $effective['week_number'])
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

        // Ajout UUID automatique si non fourni (sécurité côté contrôleur)
        $data = $validated;
        if (empty($data['uuid'])) {
            $data['uuid'] = $schedule->uuid ?? (string) \Illuminate\Support\Str::uuid();
        }

        $schedule->update($data);

        return response()->json([
            'success' => true,
            'data' => $schedule->fresh(),
            'message' => 'Horaire modifié avec succès.',
        ]);
    }

    /**
     * Delete a schedule entry.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $schoolId = $request->user()->school_id;
        $schedule = Schedule::where('school_id', $schoolId)->findOrFail($id);
        $schedule->delete();
        return response()->json([
            'success' => true,
            'message' => 'Horaire supprimé avec succès.',
        ]);
    }
}
