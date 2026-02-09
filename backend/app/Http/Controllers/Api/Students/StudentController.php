<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Students;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Classroom;
use App\Models\Registration;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
use App\Exports\StudentsExport;
use Maatwebsite\Excel\Facades\Excel;

final class StudentController extends Controller
{
    /**
     * Export students as Excel file.
     */
    public function export(Request $request)
    {
        $fileName = 'students_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new StudentsExport(), $fileName);
    }
        /**
         * Export students as PDF file.
         */
        public function exportPdf(Request $request)
        {
            $query = Student::query();
            if ($request->filled('classroom_id')) {
                $query->whereHas('registrations', function($q) use ($request) {
                    $q->where('classroom_id', $request->input('classroom_id'));
                });
            }
            $students = $query->get();

            $html = view('exports.students', [
                'students' => $students
            ])->render();

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = 'students_' . now()->format('Ymd_His') . '.pdf';

            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
        }
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        // Si super admin, utiliser l'école sélectionnée en session
        if ($user && $user->hasRole('super-admin')) {
            $schoolId = session('selected_school_id');
            if (! $schoolId) {
                return response()->json(['error' => 'Veuillez sélectionner une école avant d’accéder aux données.'], 403);
            }
        } else {
            $schoolId = $user->school_id;
        }

        // On part de la table students, filtrée par school_id
        $query = Student::where('school_id', $schoolId);

        // Recherche par mots-clés (facultatif)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('firstname', 'like', "%{$search}%")
                    ->orWhere('matricule', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Si tu as d’autres filtres génériques (optionnel)
        $this->applyFilters($query, $request, $this->getFilters());

        //  Récupération des étudiants avec pagination
        $students = $query->latest()->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'students' => $students,
        ]);
    }

    public function show(Student $student): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'student' => $student,
            ]);
        } catch (Throwable $e) {
            Log::error('Student show error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'étudiant.',
            ], 500);
        }
    }

    public function store(StudentRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Normalisation : accepter first_name / last_name si un client legacy les envoie
            if (isset($data['first_name']) && ! isset($data['firstname'])) {
                $data['firstname'] = $data['first_name'];
            }
            if (isset($data['last_name']) && ! isset($data['lastname'])) {
                $data['lastname'] = $data['last_name'];
            }

            // Gestion upload image
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('students', 'public');
                $data['image'] = $path;
            }

            /** @var \App\Models\User|null $user */
            $user = auth()->user();
            $schoolId = $user?->school_id;
            if (! $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de déterminer l'école de l'utilisateur.",
                ], 422);
            }
            $data['school_id'] = $schoolId;

            // Déduplication et logique d'inscription
            $dupQuery = Student::query()
                ->where('school_id', $schoolId)
                ->when(isset($data['name']), fn ($q) => $q->where('name', $data['name']))
                ->when(isset($data['firstname']), fn ($q) => $q->where('firstname', $data['firstname']))
                ->when(isset($data['lastname']), fn ($q) => $q->where('lastname', $data['lastname']))
                ->when(isset($data['birth_date']), fn ($q) => $q->where('birth_date', $data['birth_date']));

            $existingStudent = $dupQuery->first();
            $classroomId = $data['classroom_id'] ?? null;
            $hasRegFields = isset($data['academic_personal_id'], $data['academic_level_id'], $data['filiaire_id'], $data['cycle_id']);
            $shouldRegister = ! empty($classroomId) && $hasRegFields;

            $createdStudent = false;
            $createdRegistration = false;
            $registration = null;
            $student = $existingStudent;
            $incompleteRegistration = ! empty($classroomId) && ! $hasRegFields;

            // Validations additionnelles pour inscription one-shot
            if ($classroomId) {
                $classroom = Classroom::with('academicLevel.cycle.filiaire')->find($classroomId);
                if (! $classroom || (int) $classroom->academicLevel?->cycle?->filiaire?->school_id !== (int) $schoolId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La classe fournie est invalide ou n’appartient pas à votre école.',
                    ], 422);
                }
                // Validation du chainage réel : classe -> niveau académique -> cycle -> filière
                if (! empty($data['academic_level_id']) && (int) $classroom->academic_level_id !== (int) $data['academic_level_id']) {
                    return response()->json([
                        'success' => false,
                        'message' => "La classe sélectionnée n'est pas rattachée au niveau académique indiqué.",
                    ], 422);
                }
                $academicLevel = $classroom->academicLevel;
                if (! empty($data['cycle_id']) && ($academicLevel?->cycle?->id ?? null) !== (int) $data['cycle_id']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Le niveau académique sélectionné n'est pas rattaché au cycle indiqué.",
                    ], 422);
                }
                $cycle = $academicLevel?->cycle;
                if (! empty($data['filiaire_id']) && ($cycle?->filiaire?->id ?? null) !== (int) $data['filiaire_id']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Le cycle sélectionné n'est pas rattaché à la filière indiquée.",
                    ], 422);
                }
            }

            DB::beginTransaction();
            try {
                if (! $student) {
                    $data['matricule'] = $this->generateSimpleMatricule();
                    $student = Student::create(collect($data)->only([
                        'name', 'lastname', 'firstname', 'gender', 'birth_date', 'birth_place', 'civil_status',
                        'address', 'phone_number', 'email', 'image', 'province_id', 'territory_id', 'commune_id',
                        'parents_id', 'parent2_id', 'parent3_id', 'country_id', 'matricule', 'school_id',
                    ])->toArray());
                    $createdStudent = true;
                }

                if ($shouldRegister) {
                    $activeYear = SchoolYear::active($schoolId);
                    if (! $activeYear) {
                        DB::rollBack();

                        return response()->json([
                            'success' => false,
                            'message' => "Aucune année scolaire active pour cette école. Impossible de créer l'inscription.",
                        ], 422);
                    }

                    $alreadyRegistered = Registration::query()
                        ->where('student_id', $student->id)
                        ->where('school_year_id', $activeYear->id)
                        ->exists();

                    if (! $alreadyRegistered) {
                        $registration = Registration::create([
                            'student_id' => $student->id,
                            'school_id' => $schoolId,
                            'classroom_id' => $classroomId,
                            'school_year_id' => $activeYear->id,
                            'academic_personal_id' => auth()->user()->id,
                            'academic_level_id' => $data['academic_level_id'],
                            'filiaire_id' => $data['filiaire_id'] ?? null,
                            'cycle_id' => $data['cycle_id'] ?? null,
                            'registration_date' => now()->toDateString(),
                            'registration_status' => true,
                            'note' => $data['note'] ?? null,
                        ]);
                        $createdRegistration = true;
                    }
                }

                DB::commit();
            } catch (Throwable $txe) {
                DB::rollBack();
                throw $txe;
            }

            // Réponses contextualisées
            if ($existingStudent && ! $createdRegistration && ! $createdStudent) {
                $msg = $incompleteRegistration ? "Étudiant déjà existant (données d'inscription incomplètes)." : 'Étudiant déjà existant (aucune nouvelle inscription créée).';

                return response()->json([
                    'success' => true,
                    'duplicate' => true,
                    'student' => $student->fresh(),
                    'message' => $msg,
                ], 200);
            }

            if ($existingStudent && $createdRegistration) {
                return response()->json([
                    'success' => true,
                    'duplicate' => true,
                    'student' => $student->fresh(),
                    'registration' => $registration,
                    'message' => 'Étudiant déjà existant; inscription créée avec succès.',
                ], 201);
            }

            if ($createdStudent && $createdRegistration) {
                return response()->json([
                    'success' => true,
                    'duplicate' => false,
                    'student' => $student->fresh(),
                    'registration' => $registration,
                    'message' => 'Étudiant et inscription créés avec succès.',
                ], 201);
            }

            if ($createdStudent && ! $createdRegistration) {
                $msg = $incompleteRegistration
                    ? "Étudiant créé sans inscription (données d'inscription incomplètes)."
                    : 'Étudiant créé sans inscription (classe non fournie).';

                return response()->json([
                    'success' => true,
                    'duplicate' => false,
                    'student' => $student->fresh(),
                    'message' => $msg,
                ], 201);
            }

            return response()->json([
                'success' => true,
                'student' => $student->fresh(),
                'message' => 'Opération terminée.',
            ], 200);
        } catch (Throwable $e) {
            Log::error('Student store error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la création de l'étudiant.",
            ], 500);
        }
    }

    public function update(StudentRequest $request, Student $student): JsonResponse
    {
        try {
            $data = $request->validated();

            // Upload nouvelle image si fournie, supprimer l'ancienne
            if ($request->hasFile('image')) {
                $newPath = $request->file('image')->store('students', 'public');
                if (! empty($student->image) && Storage::disk('public')->exists($student->image)) {
                    Storage::disk('public')->delete($student->image);
                }
                $data['image'] = $newPath;
            }

            // Ne mettre à jour que les champs appartenant réellement au modèle Student.
            // Les informations académiques (classroom_id, academic_level_id, filiaire_id, cycle_id,
            // percentageObtained, etc.) sont gérées via Registration et ne doivent pas être
            // écrites dans la table students.
            $studentData = collect($data)->only([
                'matricule',
                'name',
                'lastname',
                'firstname',
                'gender',
                'birth_date',
                'birth_place',
                'civil_status',
                'address',
                'phone_number',
                'email',
                'image',
                'province_id',
                'territory_id',
                'commune_id',
                'parents_id',
                'parent2_id',
                'parent3_id',
                'country_id',
            ]);

            $student->update($studentData->toArray());

            // Mise à jour "one shot" : si des informations académiques sont fournies,
            // propager aussi la modification sur l'inscription (Registration) de l'élève.
            $classroomId = $data['classroom_id'] ?? null;
            $hasRegFields = isset($data['academic_personal_id'], $data['academic_level_id'], $data['filiaire_id'], $data['cycle_id']);

            if ($classroomId && $hasRegFields) {
                $schoolId = $student->school_id ?? auth()->user()?->school_id;
                if (! $schoolId) {
                    return response()->json([
                        'success' => false,
                        'message' => "Impossible de déterminer l'école pour mettre à jour l'inscription.",
                    ], 422);
                }

                // Validation du chaînage réel : classe -> niveau académique -> cycle -> filière -> école
                $classroom = Classroom::with('academicLevel.cycle.filiaire')->find($classroomId);
                if (! $classroom) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La classe sélectionnée est invalide.',
                    ], 422);
                }

                $academicLevelId = (int) $data['academic_level_id'];
                $cycleId = (int) $data['cycle_id'];
                $filiaireId = (int) $data['filiaire_id'];

                $academicLevel = $classroom->academicLevel;
                if (! $academicLevel || (int) $academicLevel->id !== $academicLevelId) {
                    return response()->json([
                        'success' => false,
                        'message' => "La classe sélectionnée n'est pas rattachée au niveau académique indiqué (classe → niveau académique).",
                    ], 422);
                }

                $cycle = $academicLevel->cycle;
                if (! $cycle || (int) $cycle->id !== $cycleId) {
                    return response()->json([
                        'success' => false,
                        'message' => "Le niveau académique sélectionné n'est pas rattaché au cycle indiqué (niveau académique → cycle).",
                    ], 422);
                }

                $filiaire = $cycle->filiaire;
                if (! $filiaire || (int) $filiaire->id !== $filiaireId) {
                    return response()->json([
                        'success' => false,
                        'message' => "Le cycle sélectionné n'est pas rattaché à la filière indiquée (cycle → filière).",
                    ], 422);
                }
                if ((int) $filiaire->school_id !== (int) $schoolId) {
                    return response()->json([
                        'success' => false,
                        'message' => "La filière sélectionnée n'appartient pas à l'école indiquée (filière → école).",
                    ], 422);
                }

                // Récupérer ou créer l'inscription pour l'année scolaire active
                $activeYear = SchoolYear::active((int) $schoolId);
                if (! $activeYear) {
                    return response()->json([\n+                        'success' => false,
                        'message' => "Aucune année scolaire active pour cette école. Impossible de mettre à jour l'inscription.",
                    ], 422);
                }

                $registration = Registration::query()
                    ->where('student_id', $student->id)
                    ->where('school_year_id', $activeYear->id)
                    ->first();

                $payload = [
                    'student_id' => $student->id,
                    'school_id' => $schoolId,
                    'classroom_id' => $classroomId,
                    'school_year_id' => $activeYear->id,
                    'academic_personal_id' => $data['academic_personal_id'],
                    'academic_level_id' => $academicLevelId,
                    'filiaire_id' => $filiaireId,
                    'cycle_id' => $cycleId,
                    'note' => $data['note'] ?? ($registration->note ?? null),
                ];

                if ($registration) {
                    $registration->update($payload);
                } else {
                    $payload['registration_date'] = now()->toDateString();
                    $payload['registration_status'] = true;
                    Registration::create($payload);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Étudiant mis à jour avec succès.',
                'student' => $student->fresh(),
            ]);
        } catch (Throwable $e) {
            Log::error('Student update error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'étudiant.',
            ], 500);
        }
    }

    public function destroy(Student $student): JsonResponse
    {
        try {
            $student->delete();

            return response()->json([
                'success' => true,
                'message' => 'Étudiant supprimé avec succès.',
            ]);
        } catch (Throwable $e) {
            Log::error('Student delete error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'étudiant.',
            ], 500);
        }
    }

    /**
     * Liste des filtres disponibles pour les étudiants.
     */
    private function getFilters(): array
    {
        // Filtres limités aux colonnes existantes (selon migration fournie)
        return [
            'province_id',
            'territory_id',
            'commune_id',
            'parents_id',
            'gender',
            'civil_status',
        ];
    }

    /**
     * Applique dynamiquement les filtres à la requête.
     */
    private function applyFilters(Builder $query, Request $request, array $allowedFilters): void
    {
        collect($this->getFilters())
            ->intersect($allowedFilters)
            ->each(function ($filter) use ($request, $query) {
                if ($request->filled($filter)) {
                    $query->where($filter, $request->input($filter));
                }
            });
    }

    /**
     * Géneration du username
     */
    private function generateUsername(array $data): string
    {
        $base = mb_strtolower(preg_replace('/[^a-z]/', '', $data['firstname'] ?? '').'.'.preg_replace('/[^a-z]/', '', $data['name'] ?? ''));

        if (empty(mb_trim($base))) {
            $base = mb_strstr($data['email'] ?? '', '@', true) ?? 'user';
        }

        $username = $base;
        $counter = 1;

        while (Student::where('username', $username)->exists()) {
            $username = $base.$counter;
            $counter++;
        }

        return $username;
    }

    private function generateSimpleMatricule(): string
    {
        $year = now()->year;
        $prefix = "STUD-{$year}";
        $count = Student::where('matricule', 'like', "{$prefix}-%")->count() + 1;

        return sprintf('%s-%05d', $prefix, $count);
    }
}
