<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\FicheCotation;

use App\Exports\FicheCotationExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\FicheCotationResource;
use App\Models\FicheCotation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

final class FicheCotationController extends Controller
{
    public function index(Request $request)
    {
        $classroomId = $request->input('classroom_id', $request->input('idClasse'));
        $courseId = $request->input('course_id');
        $filiaireId = $request->input('filiaire_id');

        $activeSchoolYear = \App\Models\SchoolYear::active();
        if (! $activeSchoolYear) {
            return response()->json([
                'success' => false,
                'message' => "Aucune année scolaire active n'a été trouvée.",
            ], 422);
        }

        $query = FicheCotation::with([
            'student',
            'classroom',
            'schoolYear',
            'course',
            'registration',
            'registration.schoolYear',
            'registration.student',
            'registration.classroom',
        ]);

        // Limiter aux fiches de l'année scolaire active
        $query = $query->where('school_year_id', $activeSchoolYear->id);

        // Filtrage par filière si précisé (via la hiérarchie classroom -> academicLevel -> cycle -> filiaire)
        if (! empty($filiaireId)) {
            $query = $query->whereHas('classroom.academicLevel.cycle', function ($q) use ($filiaireId) {
                $q->where('filiaire_id', $filiaireId);
            });
        }
        // Filtrage par classe si précisé
        if (! empty($classroomId)) {
            $query = $query->where('classroom_id', $classroomId);
        }
        // Filtrage par cours si précisé
        if (! empty($courseId)) {
            $query = $query->where('course_id', $courseId);
        }

        // Recherche filtrante
        if ($request->filled('search')) {
            $search = mb_strtolower(mb_trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($q2) use ($search) {
                    $q2->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(lastname) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(firstname) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(matricule) LIKE ?', ["%{$search}%"]);
                })
                ->orWhereHas('course', function ($q2) use ($search) {
                    $q2->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(label) LIKE ?', ["%{$search}%"]);
                });
            });
        }

        $cotations = $query->get();

        if ($cotations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun élève trouvé pour cette classe et ce cours.',
            ], 404);
        }

        return response()->json([
            'data' => FicheCotationResource::collection($cotations),
            'success' => true,
            'message' => 'Liste filtrée récupérée avec succès.',
            'total' => $cotations->count(),
        ], 200);
    }

    /**
     * Enregistrer ou mettre à jour des notes pour plusieurs élèves dans une classe, année scolaire et cours donnés.
     *
     * Le corps de la requête doit contenir:
     * - classroom_id (int, requis): ID de la classe
     * - course_id (int, requis): ID du cours
     * - notes (array, requis): Liste des notes à enregistrer, chaque élément doit contenir:
     *   - student_id (int, requis): ID de l'élève
     *   - note (float, requis): Note obtenue
     *
     * Exemple de payload:
     * {
     *   "classroom_id": 1,
     *   "course_id": 3,
     *   "notes": [
     *     {"student_id": 10, "note": 15.5},
     *     {"student_id": 11, "note": 18.0}
     *   ]
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws Exception
     **/
    public function store(Request $request)
    {
        $validated = $request->validate([
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'notes' => ['required', 'array'],
            'notes.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'notes.*.note' => ['required', 'array'],
        ]);

        // Utiliser l'année scolaire active pour éviter de mélanger les années
        $activeSchoolYear = \App\Models\SchoolYear::active();
        if (! $activeSchoolYear) {
            return response()->json([
                'success' => false,
                'message' => "Aucune année scolaire active n'a été trouvée.",
            ], 422);
        }

        // On ne considère que les élèves inscrits dans cette classe pour l'année scolaire active et avec inscription active
        $studentIds = \App\Models\Registration::where('classroom_id', $validated['classroom_id'])
            ->where('school_year_id', $activeSchoolYear->id)
            ->where('registration_status', true)
            ->pluck('student_id')
            ->toArray();

        if (empty($studentIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun élève inscrit dans cette classe.',
            ], 404);
        }

        $count = 0;
        foreach ($validated['notes'] as $noteData) {
            // Chercher l'inscription de l'élève dans cette classe pour l'année scolaire active
            $registration = \App\Models\Registration::where('student_id', $noteData['student_id'])
                ->where('classroom_id', $validated['classroom_id'])
                ->where('school_year_id', $activeSchoolYear->id)
                ->where('registration_status', true)
                ->first();

            if (! $registration) {
                // Élève non inscrit (ou plus inscrit) dans cette classe pour l'année active : on ignore cette ligne
                continue;
            }

            $schoolYearId = $activeSchoolYear->id;
            $fiche = FicheCotation::updateOrCreate(
                [
                    'school_year_id' => $schoolYearId,
                    'classroom_id' => $validated['classroom_id'],
                    'course_id' => $validated['course_id'],
                    'student_id' => $noteData['student_id'],
                ],
                [
                    'note' => json_encode($noteData['note']),
                ]
            );
            if ($fiche) {
                $count++;
            }
        }

        if ($count === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune fiche de cotation n\'a été enregistrée. Vérifiez les données envoyées.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fiche de cotation enregistrée avec succès.',
            'count' => $count,
        ]);
    }

    public function export(Request $request)
    {
        $classroomId = $request->input('classroom_id', $request->input('idClasse'));
        $courseId = $request->input('course_id');
        $filiaireId = $request->input('filiaire_id');

        $activeSchoolYear = \App\Models\SchoolYear::active();
        if (! $activeSchoolYear) {
            return response()->json([
                'success' => false,
                'message' => "Aucune année scolaire active n'a été trouvée.",
            ], 422);
        }

        return Excel::download(
            new FicheCotationExport(
                schoolYearId: $activeSchoolYear->id,
                classroomId: $classroomId,
                courseId: $courseId,
                filiaireId: $filiaireId,
            ),
            'fiche_cotations.xlsx'
        );
    }

    public function exportTemplate()
    {
        return Excel::download(new \App\Exports\FicheCotationTemplateExport(), 'fiche_cotations_template.xlsx');
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,csv|max:5120',
            ]);
            $file = $request->file('file');
            $imported = [];
            DB::beginTransaction();
            $rows = Excel::toArray([], $file)[0];
            // Skip header row
            $errors = [];
            $line = 1; // header at 1, data starts at 2
            foreach (array_slice($rows, 1) as $row) {
                $line++;
                // Ignore empty or incomplete rows
                if (! isset($row[0]) || ! isset($row[1]) || ! isset($row[2]) || ! isset($row[3])) {
                    // skip completely empty rows; capture partial rows as errors
                    if (empty(array_filter($row, fn ($v) => $v !== null && $v !== ''))) {
                        continue;
                    }
                    $errors[] = [
                        'line' => $line,
                        'message' => 'Colonnes requises manquantes (school_year_id, student_id, classroom_id, course_id)',
                    ];

                    continue;
                }
                // Colonnes attendues par index:
                // 0: school_year_id, 1: student_id, 2: classroom_id, 3: course_id, 4: note, 5: Maxima

                // Validate other foreign keys existence
                $schoolYearId = (int) $row[0];
                $studentId = (int) $row[1];
                $classroomId = (int) $row[2];
                $courseId = (int) $row[3];

                $fkMissing = [];
                if (! DB::table('school_years')->where('id', $schoolYearId)->exists()) {
                    $fkMissing[] = 'school_year_id';
                }
                if (! DB::table('students')->where('id', $studentId)->exists()) {
                    $fkMissing[] = 'student_id';
                }
                if (! DB::table('classrooms')->where('id', $classroomId)->exists()) {
                    $fkMissing[] = 'classroom_id';
                }
                if (! DB::table('courses')->where('id', $courseId)->exists()) {
                    $fkMissing[] = 'course_id';
                }
                if (! empty($fkMissing)) {
                    $errors[] = [
                        'line' => $line,
                        'message' => 'IDs inexistants: '.implode(', ', $fkMissing),
                    ];

                    continue;
                }

                $data = [
                    'school_year_id' => $schoolYearId,
                    'student_id' => $studentId,
                    'classroom_id' => $classroomId,
                    'course_id' => $courseId,
                ];
                $values = [
                    'note' => isset($row[4]) && $row[4] !== '' ? (float) $row[4] : 0.0,
                    'Maxima' => isset($row[5]) && $row[5] !== '' ? (float) $row[5] : 1.0,
                ];
                $imported[] = FicheCotation::updateOrCreate($data, $values);
            }
            if (! empty($errors)) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Erreurs d\'importation détectées',
                    'errors' => $errors,
                    'success' => false,
                ], 422);
            }
            DB::commit();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();

            return response()->json(['errors' => $e->errors()], 422);
        }

        $list = FicheCotation::with(['schoolYear', 'student', 'classroom', 'semester', 'course', 'registration'])->latest()->take(200)->get();

        return response()->json([
            'data' => FicheCotationResource::collection($list),
            'success' => true,
            'message' => 'Importation réussie avec succès',
        ], 201);
    }

    public function initialize(Request $request)
    {
        $classroomId = $request->input('classroom_id');
        $courseId = $request->input('course_id');
        $activeSchoolYear = \App\Models\SchoolYear::active();
        $filiaireId = $request->input('filiaire_id');

        if (empty($classroomId) || empty($courseId)) {
            return response()->json([
                'success' => false,
                'message' => 'Les paramètres classroom_id et course_id sont requis.',
            ], 422);
        }
        if (! $activeSchoolYear) {
            return response()->json([
                'success' => false,
                'message' => "Aucune année scolaire active n'a été trouvée.",
            ], 422);
        }

        // Charger la classe avec son niveau académique et le cycle via ce niveau
        $classroom = \App\Models\Classroom::with('academicLevel.cycle')->find($classroomId);
        $course = \App\Models\Course::find($courseId);
        if (! $classroom || ! $course) {
            return response()->json([
                'success' => false,
                'message' => 'Classe ou cours introuvable.',
            ], 404);
        }
        // Récupérer le niveau et le cycle via academicLevel
        $academicLevel = $classroom->academicLevel;
        $cycle = $academicLevel?->cycle;

        // Vérification explicite des relations manquantes
        $missing = [];
        if (! $academicLevel) {
            $missing[] = 'academicLevel';
        }
        if (! $cycle) {
            $missing[] = 'cycle';
        }
        if (! empty($missing)) {
            return response()->json([
                'success' => false,
                'message' => 'Relations manquantes dans la hiérarchie de la classe : '.implode(', ', $missing),
            ], 422);
        }

        // Préparer les valeurs à comparer (on se limite à cycle + niveau académique)
        $classValues = [
            'cycle_id' => $cycle ? (int) $cycle->id : null,
            'academic_level_id' => $academicLevel ? (int) $academicLevel->id : null,
        ];
        $courseValues = [
            'cycle_id' => isset($course->cycle_id) ? (int) $course->cycle_id : null,
            'academic_level_id' => isset($course->academic_level_id) ? (int) $course->academic_level_id : null,
        ];
        $fields = ['cycle_id', 'academic_level_id'];
        $correspondance = [];
        $allMatch = true;
        foreach ($fields as $field) {
            $match = ($classValues[$field] === $courseValues[$field]);
            $correspondance[$field] = [
                'classroom' => $classValues[$field],
                'course' => $courseValues[$field],
                'match' => $match,
            ];
            if (! $match) {
                $allMatch = false;
            }
        }
        if (! $allMatch) {
            return response()->json([
                'success' => false,
                'message' => "Le cours n'est pas rattaché à la même hiérarchie que la classe.",
                'correspondance' => $correspondance,
            ], 422);
        }

        // Récupérer toutes les registrations actives de la classe pour l'année scolaire active
        $registrations = \App\Models\Registration::where('classroom_id', $classroomId)
            ->where('school_year_id', $activeSchoolYear->id)
            ->where('registration_status', true)
            ->with([
                'student',
                'academicLevel',
                'classroom',
                'classroom.academicLevel.cycle',
            ])
            ->get();

        // Récupérer tous les élèves inscrits dans la classe pour cette année scolaire
        $studentIds = \App\Models\Registration::where('classroom_id', $classroomId)
            ->where('school_year_id', $activeSchoolYear->id)
            ->where('registration_status', true)
            ->pluck('student_id')
            ->unique()
            ->toArray();

        $studentsModels = \App\Models\Student::whereIn('id', $studentIds)->get()->keyBy('id');

        $created = 0;
        $initializedStudents = [];
        $details = [];
        $reasons = [];
        foreach ($studentIds as $studentId) {
            // Vérifie si une fiche existe déjà pour cet élève, cette classe, ce cours et cette année scolaire
            $exists = FicheCotation::where('student_id', $studentId)
                ->where('classroom_id', $classroomId)
                ->where('course_id', $courseId)
                ->where('school_year_id', $activeSchoolYear->id)
                ->exists();
            $details[] = [
                'student_id' => $studentId,
                'school_year_id' => $activeSchoolYear->id,
                'fiche_exists' => $exists,
            ];
            if (! $exists) {
                // Création de la fiche avec note = tableau de zéros par défaut (pas de doublon possible)
                FicheCotation::create([
                    'student_id' => $studentId,
                    'classroom_id' => $classroomId,
                    'course_id' => $courseId,
                    'school_year_id' => $activeSchoolYear->id,
                    'note' => json_encode([
                        'P1' => 0,
                        'P2' => 0,
                        'P3' => 0,
                        'P4' => 0,
                        'E1' => 0,
                        'E2' => 0,
                    ]),
                ]);
                $created++;
                $student = $studentsModels->get($studentId);
                $initializedStudents[] = [
                    'id' => $studentId,
                    'school_year_id' => $activeSchoolYear->id,
                    'name' => $student ? mb_trim(($student->firstname ?? '').' '.($student->name ?? $student->lastname ?? '')) : null,
                ];
                $reasons[] = [
                    'student_id' => $studentId,
                    'school_year_id' => $activeSchoolYear->id,
                    'reason' => 'fiche créée',
                ];
            } else {
                $reasons[] = [
                    'student_id' => $studentId,
                    'school_year_id' => $activeSchoolYear->id,
                    'reason' => 'fiche déjà existante',
                ];
            }
        }

        $diagnostic = [
            'active_school_year_id' => $activeSchoolYear->id,
            'students_ids' => $studentIds,
            'all_registrations_for_class' => $registrations,
            'details' => $details,
            'reasons' => $reasons,
        ];

        return response()->json([
            'success' => true,
            'message' => "Initialisation terminée. $created fiches créées.",
            'students' => $initializedStudents,
            'correspondance' => $correspondance,
            'diagnostic' => $diagnostic,
        ], 200);
    }
}
