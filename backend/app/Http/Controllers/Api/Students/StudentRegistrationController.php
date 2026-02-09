<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Students;


use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Http\Resources\StudentRegistrationResource;
use App\Models\Classroom;
use App\Models\Registration;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Exports\RegistrationsExport;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;

final class StudentRegistrationController extends Controller
{
    /**
     * Export registrations as Excel file.
     */
    public function export(Request $request)
    {
        $query = Registration::with([
            'student',
            'academicLevel',
            'classroom',
            'filiaire',
            'cycle',
            'schoolYear',
            'registrationParents.parent1',
            'registrationParents.parent2',
            'registrationParents.parent3',
        ]);
        
        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', (int) $request->input('classroom_id'));
        }
        
        $registrations = $query->get();
        $fileName = 'registrations_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new RegistrationsExport($registrations), $fileName);
    }
        /**
         * Export registrations as PDF file.
         */
        public function exportPdf(Request $request)
        {
            $query = Registration::with([
                'student',
                'academicLevel',
                'classroom',
                'filiaire',
                'cycle',
                'schoolYear',
                'registrationParents.parent1',
                'registrationParents.parent2',
                'registrationParents.parent3',
            ]);
            if ($request->filled('classroom_id')) {
                $query->where('classroom_id', (int) $request->input('classroom_id'));
            }
            $registrations = $query->get();

            $html = view('exports.registrations', [
                'registrations' => $registrations
            ])->render();

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            // Format paysage large pour afficher tous les champs
            $dompdf->setPaper('A2', 'landscape');
            $dompdf->render();

            $filename = 'registrations_' . now()->format('Ymd_His') . '.pdf';

            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
        }

        /**
         * GET: Liste des inscriptions (registrations), filtrable par classroom_id
         */
        public function list(Request $request)
        {
            $perPage = (int) $request->input('per_page', 30); // pagination dynamique
            $query = Registration::with(['student', 'academicLevel', 'classroom', 'filiaire', 'cycle']);
            if ($request->filled('classroom_id')) {
                $query->where('classroom_id', (int) $request->input('classroom_id'));
            }
            // Ajout du filtre search sur nom/prénom/matricule
            if ($request->filled('search')) {
                $search = mb_strtolower(mb_trim((string) $request->input('search')));
                $query->whereHas('student', function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(lastname) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(firstname) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(matricule) LIKE ?', ["%{$search}%"]);
                });
                $results = $query->get();
                return response()->json([
                    'data' => StudentRegistrationResource::collection($results),
                    'success' => true,
                    'message' => 'Résultats de la recherche',
                    'total' => $results->count(),
                ]);
            }
            $registrations = $query->paginate($perPage);
            return response()->json($registrations);
        }

    public function index(Request $request): JsonResponse
    {
        // Récupérer le nombre d'éléments par page depuis la requête, sinon 10 par défaut
        $perPage = $request->input('per_page', 10);

        // Récupérer la page actuelle depuis la requête, sinon 1 par défaut
        $page = $request->input('page', 1);

        // Base query avec relations
        $query = Registration::with(['student', 'academicLevel', 'classroom', 'filiaire', 'cycle', 'registrationParents.parent1', 'registrationParents.parent2', 'registrationParents.parent3'])->latest();

        // Filtre: filière (filiaire_id)
        if ($request->filled('filiaire_id')) {
            $query->where('filiaire_id', (int) $request->input('filiaire_id'));
        }
        // Filtre: classe (classroom_id)
        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', (int) $request->input('classroom_id'));
        }
        // Filtre: cycle (cycle_id) si utile
        if ($request->filled('cycle_id')) {
            $query->where('cycle_id', (int) $request->input('cycle_id'));
        }
        // Filtre: genre de l'élève (gender). On normalise en majuscule pour éviter les soucis de casse.
        if ($request->filled('gender')) {
            $gender = mb_strtoupper(mb_trim((string) $request->input('gender')));
            $query->whereHas('student', fn ($q) => $q->whereRaw('UPPER(gender) = ?', [$gender]));
        }
        // Recherche sur nom, prénom, matricule (search)
        if ($request->filled('search')) {
            $search = mb_strtolower(mb_trim((string) $request->input('search')));
            $query->whereHas('student', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(lastname) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(firstname) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(matricule) LIKE ?', ["%{$search}%"]);
            });
        }
        // Gestion d'un flag distinct_student=true pour ne garder qu'une inscription par élève (la plus récente)
        if ($request->boolean('distinct_student')) {
            // Sous-requête pour récupérer l'id d'inscription la plus récente par élève
            $latestIds = Registration::selectRaw('MAX(id) as id')->groupBy('student_id');
            $query->whereIn('id', $latestIds->pluck('id'));
        }

        // Si recherche, retourner uniquement les résultats correspondants (pas de pagination globale inutile)
        if ($request->filled('search')) {
            $results = $query->get();
            // Si aucun résultat, retourner un tableau vide sans pagination
            return response()->json([
                'data' => StudentRegistrationResource::collection($results),
                'success' => true,
                'message' => 'Résultats de la recherche',
                'total' => $results->count(),
            ], Response::HTTP_OK);
        }

        // Sinon, pagination classique
        $registrations = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'data' => StudentRegistrationResource::collection($registrations),
            'success' => true,
            'message' => 'Student Registration List',
            'meta' => [
                'current_page' => $registrations->currentPage(),
                'last_page' => $registrations->lastPage(),
                'per_page' => $registrations->perPage(),
                'total' => $registrations->total(),
            ],
        ], Response::HTTP_OK);
    }

    // Nouveau: liste des inscriptions d'un élève précis
    public function showForStudent(Student $student): JsonResponse
    {
        $registrations = Registration::query()
            ->with(['student', 'academicLevel', 'classroom', 'filiaire', 'cycle', 'registrationParents.parent1', 'registrationParents.parent2', 'registrationParents.parent3'])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => "Inscriptions de l'élève",
            'data' => StudentRegistrationResource::collection($registrations),
        ], Response::HTTP_OK);
    }

    public function store(RegistrationRequest $request, ?Student $student = null): JsonResponse
    {
        $data = $request->validated();

        // 1. Résolution student_id (priorité au student injecté dans l'URL)
        $studentId = $student?->id ?? $data['student_id'] ?? null;
        if (! $studentId) {
            return response()->json([
                'success' => false,
                'message' => 'student_id manquant.',
            ], 422);
        }

        // 2. Déterminer school_id (ordre: payload -> user -> student)
        $user = Auth::user();
        $schoolId = $data['school_id'] ?? $user?->school_id ?? null;
        if (! $schoolId && $student) {
            $schoolId = $student->school_id ?? null;
        }
        if (! $schoolId) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de déterminer school_id (fournissez school_id ou rattachez l’utilisateur à une école).',
            ], 422);
        }

        // 3. Validation du chaînage réel : classe -> niveau académique -> cycle -> filière -> école
        $classroomId = $data['classroom_id'] ?? null;
        $academicLevelId = $data['academic_level_id'] ?? null;
        $cycleId = $data['cycle_id'] ?? null;
        $filiaireId = $data['filiaire_id'] ?? null;
        if ($classroomId && $academicLevelId && $cycleId && $filiaireId && $schoolId) {
            $classroom = \App\Models\Classroom::with('academicLevel.cycle.filiaire')->find($classroomId);
            if (! $classroom) {
                return response()->json([
                    'success' => false,
                    'message' => 'La classe sélectionnée est invalide.',
                ], 422);
            }
            $academicLevel = $classroom->academicLevel;
            if (! $academicLevel || $academicLevel->id != $academicLevelId) {
                return response()->json([
                    'success' => false,
                    'message' => "La classe sélectionnée n'est pas rattachée au niveau académique indiqué (classe → niveau académique).",
                ], 422);
            }
            $cycle = $academicLevel->cycle;
            if (! $cycle || $cycle->id != $cycleId) {
                return response()->json([
                    'success' => false,
                    'message' => "Le niveau académique sélectionné n'est pas rattaché au cycle indiqué (niveau académique → cycle).",
                ], 422);
            }
            $filiaire = $cycle->filiaire;
            if (! $filiaire || $filiaire->id != $filiaireId) {
                return response()->json([
                    'success' => false,
                    'message' => "Le cycle sélectionné n'est pas rattaché à la filière indiquée (cycle → filière).",
                ], 422);
            }
            if ($filiaire->school_id != $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => "La filière sélectionnée n'appartient pas à l'école indiquée (filière → école).",
                ], 422);
            }
        }

        // 4. Déterminer l'année scolaire (payload sinon active)
        $schoolYearId = $data['school_year_id'] ?? SchoolYear::active((int) $schoolId)?->id;
        $activeYear = $schoolYearId ? SchoolYear::find($schoolYearId) : null; // récupération objet année
        if (! $activeYear) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune année scolaire active trouvée pour cette école et aucune school_year_id fournie.',
            ], 422);
        }

        // 5. Prévenir doublon pour même élève + même année scolaire
        $duplicate = Registration::query()
            ->where('student_id', $studentId)
            ->where('school_year_id', $schoolYearId)
            ->exists();
        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'Cet élève est déjà inscrit pour cette année scolaire.',
                'school_year' => $activeYear,
            ], 422);
        }

        // 6. Résolution ou création des parents optionnels
        $parent1Id = $data['parent1_id'] ?? null;
        if (!$parent1Id) {
            $parent1Name = $data['parent1_name'] ?? null;
            if (empty($parent1Name) && !empty($data['parent1_email'])) {
                // Déduire le nom à partir de l'email (avant le @)
                $parent1Name = strstr($data['parent1_email'], '@', true);
            }
            if (!empty($parent1Name) && !empty($data['parent1_email'])) {
                $parent1 = \App\Models\Parents::firstOrCreate(
                    [
                        'name' => $parent1Name,
                        'email1' => $data['parent1_email'],
                    ],
                    [
                        'phone1' => $data['parent1_phone'] ?? null,
                    ]
                );
                $parent1Id = $parent1->id;
            }
        }

        $parent2Id = $data['parent2_id'] ?? null;
        if (!$parent2Id) {
            $parent2Name = $data['parent2_name'] ?? null;
            if (empty($parent2Name) && !empty($data['parent2_email'])) {
                $parent2Name = strstr($data['parent2_email'], '@', true);
            }
            if (!empty($parent2Name) && !empty($data['parent2_email'])) {
                $parent2 = \App\Models\Parents::firstOrCreate(
                    [
                        'name' => $parent2Name,
                        'email1' => $data['parent2_email'],
                    ],
                    [
                        'phone1' => $data['parent2_phone'] ?? null,
                    ]
                );
                $parent2Id = $parent2->id;
            }
        }

        $parent3Id = $data['parent3_id'] ?? null;
        if (!$parent3Id) {
            $parent3Name = $data['parent3_name'] ?? null;
            if (empty($parent3Name) && !empty($data['parent3_email'])) {
                $parent3Name = strstr($data['parent3_email'], '@', true);
            }
            if (!empty($parent3Name) && !empty($data['parent3_email'])) {
                $parent3 = \App\Models\Parents::firstOrCreate(
                    [
                        'name' => $parent3Name,
                        'email1' => $data['parent3_email'],
                    ],
                    [
                        'phone1' => $data['parent3_phone'] ?? null,
                    ]
                );
                $parent3Id = $parent3->id;
            }
        }

        // 7. Construction payload (type_id auto si absent via trait)
        $payload = [
            'student_id' => $studentId,
            'school_id' => $schoolId,
            'classroom_id' => $classroomId,
            'school_year_id' => $schoolYearId,
            'academic_personal_id' => $data['academic_personal_id'],
            'academic_level_id' => $data['academic_level_id'],
            // Nouvelles colonnes
            'filiaire_id' => $data['filiaire_id'],
            'cycle_id' => $data['cycle_id'],
            'registration_date' => now()->toDateString(),
            'registration_status' => true,
            'note' => $data['note'] ?? null,
        ];

        $registration = DB::transaction(function () use ($payload, $parent1Id, $parent2Id, $parent3Id) {
            $reg = Registration::create($payload);
            \App\Models\RegistrationParents::create([
                'registration_id' => $reg->id,
                'parent1_id' => $parent1Id,
                'parent2_id' => $parent2Id,
                'parent3_id' => $parent3Id,
            ]);

            return $reg;
        });
        // Charger les relations pour la réponse
        $registration->load(['student', 'academicLevel', 'classroom', 'filiaire', 'cycle', 'registrationParents.parent1', 'registrationParents.parent2', 'registrationParents.parent3']);

        return response()->json([
            'success' => true,
            'message' => 'Inscription enregistrée avec succès.',
            'registration' => $registration,
            'school_year' => $activeYear,
        ], 201);
    }

    public function update(RegistrationRequest $request, Registration $registration): JsonResponse
    {
        $data = $request->validated();

        $studentId = $registration->student_id;
        $user = Auth::user();
        $schoolId = $registration->school_id ?? $data['school_id'] ?? $user?->school_id;
        if (! $schoolId) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de déterminer school_id (fournissez school_id ou rattachez l’utilisateur à une école).',
            ], 422);
        }

        $classroomId = $data['classroom_id'] ?? $registration->classroom_id;
        $academicLevelId = $data['academic_level_id'] ?? $registration->academic_level_id;
        $cycleId = $data['cycle_id'] ?? $registration->cycle_id;
        $filiaireId = $data['filiaire_id'] ?? $registration->filiaire_id;

        if ($classroomId && $academicLevelId && $cycleId && $filiaireId && $schoolId) {
            $classroom = Classroom::with('academicLevel.cycle.filiaire')->find($classroomId);
            if (! $classroom) {
                return response()->json([
                    'success' => false,
                    'message' => 'La classe sélectionnée est invalide.',
                ], 422);
            }
            $academicLevel = $classroom->academicLevel;
            if (! $academicLevel || $academicLevel->id != $academicLevelId) {
                return response()->json([
                    'success' => false,
                    'message' => "La classe sélectionnée n'est pas rattachée au niveau académique indiqué (classe → niveau académique).",
                ], 422);
            }
            $cycle = $academicLevel->cycle;
            if (! $cycle || $cycle->id != $cycleId) {
                return response()->json([
                    'success' => false,
                    'message' => "Le niveau académique sélectionné n'est pas rattaché au cycle indiqué (niveau académique → cycle).",
                ], 422);
            }
            $filiaire = $cycle->filiaire;
            if (! $filiaire || $filiaire->id != $filiaireId) {
                return response()->json([
                    'success' => false,
                    'message' => "Le cycle sélectionné n'est pas rattaché à la filière indiquée (cycle → filière).",
                ], 422);
            }
            if ($filiaire->school_id != $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => "La filière sélectionnée n'appartient pas à l'école indiquée (filière → école).",
                ], 422);
            }
        }

        $schoolYearId = $registration->school_year_id;
        $activeYear = $schoolYearId ? SchoolYear::find($schoolYearId) : null;
        if (! $activeYear) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune année scolaire active trouvée pour cette école et aucune school_year_id fournie.',
            ], 422);
        }

        $payload = [
            'student_id' => $studentId,
            'school_id' => $schoolId,
            'classroom_id' => $classroomId,
            'school_year_id' => $schoolYearId,
            'academic_personal_id' => $data['academic_personal_id'] ?? $registration->academic_personal_id,
            'academic_level_id' => $academicLevelId,
            'filiaire_id' => $filiaireId,
            'cycle_id' => $cycleId,
            'note' => $data['note'] ?? $registration->note,
        ];

        $parent1Id = $data['parent1_id'] ?? null;
        $parent2Id = $data['parent2_id'] ?? null;
        $parent3Id = $data['parent3_id'] ?? null;

        $registration = DB::transaction(function () use ($registration, $payload, $parent1Id, $parent2Id, $parent3Id) {
            $registration->update($payload);

            $regParents = $registration->registrationParents()->first();
            if ($regParents) {
                $regParents->update([
                    'parent1_id' => $parent1Id,
                    'parent2_id' => $parent2Id,
                    'parent3_id' => $parent3Id,
                ]);
            } else {
                \App\Models\RegistrationParents::create([
                    'registration_id' => $registration->id,
                    'parent1_id' => $parent1Id,
                    'parent2_id' => $parent2Id,
                    'parent3_id' => $parent3Id,
                ]);
            }

            return $registration;
        });

        $registration->load(['student', 'academicLevel', 'classroom', 'filiaire', 'cycle', 'registrationParents.parent1', 'registrationParents.parent2', 'registrationParents.parent3']);

        return response()->json([
            'success' => true,
            'message' => 'Inscription mise à jour avec succès.',
            'registration' => $registration,
            'school_year' => $activeYear,
        ], Response::HTTP_OK);
    }

    public function classroomsForMySchool(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $schoolId = $user?->school_id;
        if (! $schoolId) {
            return response()->json([
                'success' => false,
                'message' => "Impossible de déterminer l'école de l'utilisateur.",
            ], 422);
        }

        $query = Classroom::query()
            ->where('school_id', $schoolId)
            ->with(['filiaire.cycles.academicLevels']);

        if ($request->filled('filiaire_id')) {
            $query->where('filiaire_id', (int) $request->input('filiaire_id'));
        }
        if ($request->filled('academic_level_id')) {
            $levelId = (int) $request->input('academic_level_id');
            $query->whereHas('filiaire.cycles.academicLevels', fn ($q) => $q->where('id', $levelId));
        }
        if ($request->filled('cycle_id')) {
            $cycleId = (int) $request->input('cycle_id');
            $query->whereHas('filiaire.cycles', fn ($q) => $q->where('id', $cycleId));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $classrooms = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $classrooms,
            'message' => 'Classes de votre école',
        ]);
    }

    public function classroomsForStudent(Student $student, Request $request): JsonResponse
    {
        if (! $student->school_id) {
            return response()->json([
                'success' => false,
                'message' => "L'élève n'est pas rattaché à une école.",
            ], 422);
        }

        $query = Classroom::query()
            ->where('school_id', $student->school_id)
            ->with(['filiaire.cycles.academicLevels']);

        if ($request->filled('filiaire_id')) {
            $query->where('filiaire_id', (int) $request->input('filiaire_id'));
        }
        if ($request->filled('academic_level_id')) {
            $levelId = (int) $request->input('academic_level_id');
            $query->whereHas('filiaire.cycles.academicLevels', fn ($q) => $q->where('id', $levelId));
        }
        if ($request->filled('cycle_id')) {
            $cycleId = (int) $request->input('cycle_id');
            $query->whereHas('filiaire.cycles', fn ($q) => $q->where('id', $cycleId));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $classrooms = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $classrooms,
            'message' => "Classes de l'école de l'élève",
        ]);
    }
}
