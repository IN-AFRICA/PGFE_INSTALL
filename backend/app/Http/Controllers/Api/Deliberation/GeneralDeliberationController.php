<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Deliberation;

use App\Http\Controllers\Controller;
use App\Enums\GenderEnum;
use App\Models\Course;
use App\Models\Deliberation;
use App\Models\FicheCotation;
use App\Models\Registration;
use App\Models\Student;
use App\Models\ValidationAureat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

final class GeneralDeliberationController extends Controller
{
    /**
     * GET /api/v1/deliberations/general/students/{student}
     * Calcule la délibération générale d'un élève pour sa classe/année.
     * Query params optionnels:
     * - classroom_id: classe ciblée (sinon inscription la plus récente)
     * - school_year_id: année scolaire ciblée (sinon celle de l'inscription retenue)
     * - threshold: seuil de validation en %, défaut 50
     * - weight_by_hourly: pondération par volume horaire (bool), défaut false
     * - skip_missing: ignorer les cours sans fiche de cotation (bool), défaut false
     */
    public function showForStudent(Request $request, int $student): JsonResponse
    {
        $threshold = (float) $request->input('threshold', 50);
        $weightByHourly = $request->boolean('weight_by_hourly', false);
        $skipMissing = $request->boolean('skip_missing', false);

        $student = Student::find($student);
        if (! $student) {
            return Response::json(['success' => false, 'message' => 'Élève introuvable'], 404);
        }

        $classroomId = $request->input('classroom_id');
        $schoolYearId = $request->input('school_year_id');

        // Choix de l'inscription de référence
        $registration = Registration::query()
            ->when($classroomId, fn ($q) => $q->where('classroom_id', (int) $classroomId))
            ->when($schoolYearId, fn ($q) => $q->where('school_year_id', (int) $schoolYearId))
            ->where('student_id', $student->id)
            ->orderByDesc('registration_status')
            ->orderByDesc('id')
            ->first();

        if (! $registration) {
            return Response::json([
                'success' => false,
                'message' => "Aucune inscription trouvée pour l'élève avec les critères fournis.",
            ], 422);
        }

        $classroomId = (int) ($classroomId ?: $registration->classroom_id);
        $schoolYearId = (int) ($schoolYearId ?: $registration->school_year_id);

        $result = $this->computeGeneralDeliberation($student, $classroomId, $schoolYearId, $threshold, $weightByHourly, $skipMissing);

        if ($result['courses_empty']) {
            if (($result['overall_percentage'] ?? 0) >= 80) {
                $this->markStudentAsLaureatFromDeliberation($student, $classroomId, $schoolYearId, $result);
            }

            return Response::json([
                'success' => true,
                'message' => 'Aucun cours défini pour cette classe.',
                'data' => [
                    'student_id' => $student->id,
                    'classroom_id' => $classroomId,
                    'school_year_id' => $schoolYearId,
                    'overall_percentage' => 0.0,
                    'validated' => false,
                    'threshold' => $threshold,
                    'courses' => [],
                ],
            ]);
        }

        return Response::json([
            'success' => true,
            'message' => 'Délibération générale calculée',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => mb_trim(($student->firstname ?? '').' '.($student->name ?? $student->lastname ?? '')),
                ],
                'classroom_id' => $classroomId,
                'school_year_id' => $schoolYearId,
                'overall_percentage' => $result['overall_percentage'],
                'validated' => $result['validated'],
                'threshold' => $threshold,
                'method' => $weightByHourly ? 'weighted_by_hourly' : 'simple_average',
                'counts' => $result['counts'],
                'breakdown' => $result['breakdown'],
                'course_percentages' => $result['course_percentages'],
                'eligible_for_validation' => $result['validated'],
            ],
        ]);
    }

    /**
     * POST /api/v1/deliberations/general/students/{student}/validate
     * Valide en masse les délibérations cours d'un élève pour une classe/année.
     * Body: { classroom_id, school_year_id }
     */
    public function validateForStudent(Request $request, int $student): JsonResponse
    {
        $payload = $request->validate([
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'school_year_id' => ['required', 'integer', 'exists:school_years,id'],
            'threshold' => ['sometimes', 'numeric', 'min:0'],
            'weight_by_hourly' => ['sometimes', 'boolean'],
            'skip_missing' => ['sometimes', 'boolean'],
            'force' => ['sometimes', 'boolean'],
        ]);

        $student = Student::find($student);
        if (! $student) {
            return Response::json(['success' => false, 'message' => 'Élève introuvable'], 404);
        }

        $threshold = (float) ($payload['threshold'] ?? 50);
        $weightByHourly = (bool) ($payload['weight_by_hourly'] ?? false);
        $skipMissing = (bool) ($payload['skip_missing'] ?? false);
        $force = (bool) ($payload['force'] ?? false);

        $classroomId = (int) $payload['classroom_id'];
        $schoolYearId = (int) $payload['school_year_id'];

        // Vérifier inscription cohérente (optionnel, on peut garder)
        $registrationExists = Registration::query()
            ->where('student_id', $student->id)
            ->where('classroom_id', $classroomId)
            ->where('school_year_id', $schoolYearId)
            ->exists();
        if (! $registrationExists) {
            return Response::json([
                'success' => false,
                'message' => "L'élève n'a pas d'inscription correspondant à classroom_id/school_year_id.",
            ], 422);
        }

        // Calculer la délibération générale avant validation
        $result = $this->computeGeneralDeliberation($student, $classroomId, $schoolYearId, $threshold, $weightByHourly, $skipMissing);

        if (! $force && ! $result['validated']) {
            return Response::json([
                'success' => false,
                'message' => 'Validation refusée: pourcentage général en-dessous du threshold.',
                'overall_percentage' => $result['overall_percentage'],
                'threshold' => $threshold,
            ], 422);
        }

        // Mise à jour uniquement du champ is_validated
        $updated = Deliberation::query()
            ->where('student_id', $student->id)
            ->where('classroom_id', $classroomId)
            ->where('school_year_id', $schoolYearId)
            ->update(['is_validated' => true]);

        $overallAtLeastEighty = ($result['overall_percentage'] ?? 0) >= 80;

        if ($overallAtLeastEighty) {
            $this->markStudentAsLaureatFromDeliberation($student, $classroomId, $schoolYearId, $result);
        }

        return Response::json([
            'success' => true,
            'message' => 'Délibérations cours validées',
            'updated_count' => $updated,
            'overall_percentage' => $result['overall_percentage'],
            'threshold' => $threshold,
            'forced' => $force,
            'course_percentages' => $result['course_percentages'],
        ]);
    }

    public function markStudentAsLaureatFromDeliberation(Student $student, int $classroomId, int $schoolYearId, array $result): void
    {
        $registration = Registration::query()
            ->where('student_id', $student->id)
            ->where('classroom_id', $classroomId)
            ->where('school_year_id', $schoolYearId)
            ->orderByDesc('registration_status')
            ->orderByDesc('id')
            ->first();

        $classroom = $registration?->classroom;
        $filiaire = $registration?->filiaire;
        $cycle = $registration?->cycle;
        $schoolYear = $registration?->schoolYear;

        $genderValue = null;
        $genderEnum = $student->gender ?? null;
        if ($genderEnum instanceof GenderEnum) {
            $genderValue = match ($genderEnum) {
                GenderEnum::MA => 'male',
                GenderEnum::FA => 'female',
                default => 'other',
            };
        } elseif (is_string($genderEnum)) {
            $g = mb_strtolower(mb_trim($genderEnum));
            if (in_array($g, ['m', 'masculin', 'male'], true)) {
                $genderValue = 'male';
            } elseif (in_array($g, ['f', 'feminin', 'féminin', 'female'], true)) {
                $genderValue = 'female';
            } else {
                $genderValue = 'other';
            }
        }

        $lastName = $student->lastname ?? $student->name ?? '';
        $firstName = $student->firstname ?? '';

        $data = [
            'last_name' => $lastName !== '' ? $lastName : 'Inconnu',
            'middle_name' => null,
            'first_name' => $firstName !== '' ? $firstName : 'Inconnu',
            'registration_number' => $student->matricule ?: (string) $student->id,
            'gender' => $genderValue ?? 'other',
            'department' => $filiaire?->name ?? '',
            'class' => $classroom?->name ?? '',
            'year' => $schoolYear?->name ?? '',
            'cycle' => $cycle?->name ?? '',
            'present' => true,
            'comment' => 'Généré automatiquement à partir des délibérations',
            'percentage' => (int) round($result['overall_percentage'] ?? 0),
        ];

        $uniqueKey = [
            'class' => $data['class'],
            'year' => $data['year'],
        ];

        $uniqueKey['registration_number'] = $data['registration_number'];

        ValidationAureat::updateOrCreate($uniqueKey, $data);
    }

    public function computeGeneralDeliberation(Student $student, int $classroomId, int $schoolYearId, float $threshold, bool $weightByHourly, bool $skipMissing): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Course> $courses */
        $courses = Course::query()->where('classroom_id', $classroomId)->get();
        $coursesArray = $courses->all();
        if (count($coursesArray) === 0) {
            return [
                'courses_empty' => true,
                'overall_percentage' => 0.0,
                'validated' => false,
                'breakdown' => [],
                'counts' => [
                    'total_courses' => 0,
                    'included' => 0,
                    'skipped' => 0,
                ],
            ];
        }

        $breakdown = [];
        $coursePercentages = [];
        $weightedSum = 0.0;
        $totalWeight = 0.0;
        $includedCount = 0;
        $skippedCount = 0;

        foreach ($courses as $course) {
            $fiche = FicheCotation::query()
                ->where('student_id', $student->id)
                ->where('classroom_id', $classroomId)
                ->where('course_id', $course->id)
                ->where('school_year_id', $schoolYearId)
                ->orderByDesc('id')
                ->first();

            if (! $fiche && $skipMissing) {
                $skippedCount++;
                $breakdown[] = [
                    'course_id' => $course->id,
                    'label' => $course->label,
                    'percentage' => null,
                ];

                continue;
            }

            $courseMaxima = [
                'P1' => (float) ($course->max_period_1 ?? 0),
                'P2' => (float) ($course->max_period_2 ?? 0),
                'P3' => (float) ($course->max_period_3 ?? 0),
                'P4' => (float) ($course->max_period_4 ?? 0),
                'E1' => (float) ($course->max_exam_1 ?? 0),
                'E2' => (float) ($course->max_exam_2 ?? 0),
            ];
            $sumMaxCourse = array_sum($courseMaxima);
            if ($sumMaxCourse <= 0) {
                $courseGlobalMax = (float) ($course->maxima ?? 0);
                if ($courseGlobalMax > 0) {
                    $sumMaxCourse = $courseGlobalMax;
                }
            }

            $noteKeys = ['P1', 'P2', 'P3', 'P4', 'E1', 'E2'];
            $note = [];
            if ($fiche) {
                $rawNote = $fiche->note ?? null;
                $parsed = null;
                if (is_string($rawNote)) {
                    $decoded = json_decode($rawNote, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $parsed = $decoded;
                    }
                } elseif (is_array($rawNote)) {
                    $parsed = $rawNote;
                }
                if (! is_array($parsed)) {
                    $parsed = [];
                }
                $normalized = [];
                foreach ($parsed as $k => $v) {
                    $normalized[mb_strtoupper((string) $k)] = $v;
                }
                foreach ($noteKeys as $k) {
                    $note[$k] = (float) ($normalized[$k] ?? 0.0);
                }
            } else {
                foreach ($noteKeys as $k) {
                    $note[$k] = 0.0;
                }
            }

            $sum = 0.0;
            foreach ($noteKeys as $k) {
                $sum += (float) $note[$k];
            }

            $percentage = $sumMaxCourse > 0 ? round(($sum / $sumMaxCourse) * 100, 2) : 0.0;
            $weight = 1.0;
            if ($weightByHourly) {
                $hv = (float) ($course->hourly_volume ?? 0);
                $weight = $hv > 0 ? $hv : 1.0;
            }

            $weightedSum += $percentage * $weight;
            $totalWeight += $weight;
            $includedCount++;

            $breakdown[] = [
                'course_id' => $course->id,
                'label' => $course->label,
                'percentage' => $percentage,
            ];
            $coursePercentages[] = [
                'course_id' => $course->id,
                'percentage' => $percentage,
            ];
        }

        $overall = $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0.0;
        $validated = $overall >= $threshold;

        return [
            'courses_empty' => false,
            'overall_percentage' => $overall,
            'validated' => $validated,
            'breakdown' => $breakdown,
            'course_percentages' => $coursePercentages,
            'counts' => [
                'total_courses' => count($coursesArray),
                'included' => $includedCount,
                'skipped' => $skippedCount,
            ],
        ];
    }
}
