<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Courses;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Cycle;
use App\Models\Filiaire;
use Exception;
use App\Exports\CoursesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use function data_get;

final class CourseController extends Controller
{
    /**
     * Export courses as Excel file.
     */
    public function export(Request $request)
    {
        $fileName = 'courses_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new CoursesExport(), $fileName);
    }
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user || ! data_get($user, 'school_id')) {
            return response()->json([
                'success' => false,
                'message' => "Impossible de déterminer l'école de l'utilisateur.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $query = Course::query()
            ->with([
                'academicPersonals',
                'academicLevel.cycle.filiaire.school',
                'filiaire.cycles.academicLevels.classrooms',
                'classroom.academicLevel.cycle.filiaire.school',
            ]);

        // Filtres optionnels
        if ($request->filled('academic_level_id') && is_numeric($request->input('academic_level_id'))) {
            $query->where('academic_level_id', (int) $request->input('academic_level_id'));
        }
        if ($request->filled('filiaire_id') && is_numeric($request->input('filiaire_id'))) {
            $query->where('filiaire_id', (int) $request->input('filiaire_id'));
        }
        if ($request->filled('cycle_id') && is_numeric($request->input('cycle_id'))) {
            $query->where('cycle_id', (int) $request->input('cycle_id'));
        }
        if ($request->filled('classroom_id') && is_numeric($request->input('classroom_id'))) {
            $query->where('classroom_id', (int) $request->input('classroom_id'));
        }
        if ($request->filled('teacher_id') && is_numeric($request->input('teacher_id'))) {
            $query->where('teacher_id', (int) $request->input('teacher_id'));
        }
        if ($request->filled('author_id') && is_numeric($request->input('author_id'))) {
            $query->where('author_id', (int) $request->input('author_id'));
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $courses = $query->latest('id')->paginate($perPage);

        return response()->json([
            'data' => $courses,
            'message' => 'Cours recuperer avec succes',
            'success' => true,
        ], Response::HTTP_OK);
    }

    public function store(\App\Http\Requests\CourseRequest $request): JsonResponse|Exception
    {
        try {
            $user = Auth::user();
            $schoolId = (int) data_get($user, 'school_id');
            if (! $user || ! $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de déterminer l'école de l'utilisateur.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data = $request->validated();
            // Forcer l'école depuis l'utilisateur connecté (ne pas faire confiance au client)
            $data['school_id'] = $schoolId;

            // Vérification du chaînage school -> filiaire -> cycle -> academic_level -> classroom
            $filiaireOk = Filiaire::query()
                ->whereKey($data['filiaire_id'])
                ->where('school_id', $schoolId)
                ->exists();

            $cycleOk = Cycle::query()
                ->whereKey($data['cycle_id'])
                ->where('filiaire_id', $data['filiaire_id'])
                ->exists();

            $levelOk = AcademicLevel::query()
                ->whereKey($data['academic_level_id'])
                ->where('cycle_id', $data['cycle_id'])
                ->exists();

            $classroomOk = Classroom::query()
                ->whereKey($data['classroom_id'])
                ->where('academic_level_id', $data['academic_level_id'])
                ->exists();

            if (! ($cycleOk && $levelOk && $filiaireOk && $classroomOk)) {
                return response()->json([
                    'success' => false,
                    'message' => "Le cycle, le niveau, la filière ou la classe n'appartient pas à votre école ou ne respecte pas le chaînage.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $course = Course::create($data);

            return response()->json([
                'data' => $course->load([
                    'academicLevel.cycle.filiaire.school',
                    'filiaire.cycles.academicLevels.classrooms',
                    'classroom.academicLevel.cycle.filiaire.school',
                ]),
                'message' => 'Coures cree avec succees',
                'success' => true], Response::HTTP_CREATED);

        } catch (Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue '.$e->getMessage()]);
        }

    }

    public function show(int $id): JsonResponse
    {
        try {
            $course = Course::with([
                'academicPersonals',
                'academicLevel.cycle.filiaire.school',
                'filiaire.cycles.academicLevels.classrooms',
                'classroom.academicLevel.cycle.filiaire.school',
            ])->find($id);
            if (! $course) {
                return response()->json([
                    'message' => 'Aucun cours avec cet Id',
                    'success' => false,
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'data' => $course,
                'message' => 'Cours recuperer avec success',
                'success' => true], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'une erreur est survenue lors de la recuperation du cours '.$e->getMessage()]);
        }

    }

    public function update(\App\Http\Requests\CourseRequest $request, int $id): JsonResponse
    {
        try {
            $course = Course::find($id);
            if (! $course) {
                return response()->json([
                    'message' => 'Aucun cours avec cet Id',
                    'success' => false,
                ], Response::HTTP_NOT_FOUND);
            }

            $user = Auth::user();
            $schoolId = (int) data_get($user, 'school_id');
            if (! $user || ! $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de déterminer l'école de l'utilisateur.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data = $request->validated();
            $data['school_id'] = $schoolId; // forcer l'école côté serveur
            $filiaireOk = true;
            $cycleOk = true;
            $levelOk = true;
            $classroomOk = true;
            if (array_key_exists('filiaire_id', $data)) {
                $filiaireOk = Filiaire::query()
                    ->whereKey($data['filiaire_id'])
                    ->where('school_id', $schoolId)
                    ->exists();
            }
            if (array_key_exists('cycle_id', $data) && array_key_exists('filiaire_id', $data)) {
                $cycleOk = Cycle::query()
                    ->whereKey($data['cycle_id'])
                    ->where('filiaire_id', $data['filiaire_id'])
                    ->exists();
            }
            if (array_key_exists('academic_level_id', $data) && array_key_exists('cycle_id', $data)) {
                $levelOk = AcademicLevel::query()
                    ->whereKey($data['academic_level_id'])
                    ->where('cycle_id', $data['cycle_id'])
                    ->exists();
            }
            if (array_key_exists('classroom_id', $data) && array_key_exists('academic_level_id', $data)) {
                $classroomOk = Classroom::query()
                    ->whereKey($data['classroom_id'])
                    ->where('academic_level_id', $data['academic_level_id'])
                    ->exists();
            }

            if (! ($cycleOk && $levelOk && $filiaireOk && $classroomOk)) {
                return response()->json([
                    'success' => false,
                    'message' => "Le cycle, le niveau, la filière ou la classe n'appartient pas à votre école ou ne respecte pas le chaînage.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $course->update($data);

            return response()->json([
                'data' => $course->fresh([
                    'academicLevel.cycle.filiaire.school',
                    'filiaire.cycles.academicLevels.classrooms',
                    'classroom.academicLevel.cycle.filiaire.school',
                ]),
                'message' => 'Cours mis a jour avec succees ',
                'success' => true], Response::HTTP_ACCEPTED);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors de la mise a jour du cours '.$e->getMessage(),
            ]);
        }

    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $course = Course::find($id);
            if (! $course) {
                return response()->json([
                    'message' => 'Aucun cours avec cet Id',
                    'success' => false,
                ], Response::HTTP_NOT_FOUND);
            }
            $course->delete();
            return response()->json([
                'message' => 'Cours supprime avec succes',
                'success' => true], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de la suppression du cours '.$e->getMessage()]);
        }
    }
}
