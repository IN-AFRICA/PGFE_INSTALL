<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Presences;

use App\Exports\PresencesExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\PresenceRessource;
use App\Models\Classroom;
use App\Models\Presence;
use App\Models\Registration;
use App\Models\Student;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

final class PresenceController extends Controller
{
    /**
     * Liste par classe: retourne tous les élèves de la classe (classroom_id|idClasse) avec leur statut de présence pour la date.
     * Paramètres query requis: classroom_id (ou idClasse); optionnels: date (YYYY-MM-DD, défaut aujourd'hui), school_id, filiere_id.
     */
    public function index(Request $request)
    {
        $query = Presence::with(['student', 'school', 'classroom'])
            ->leftJoin('students', 'students.id', '=', 'presences.student_id')
            ->select('presences.*');

        // Scoping école: school_id param ou école de l'utilisateur
        $schoolId = $request->input('school_id', data_get(Auth::user(), 'school_id'));
        if (! empty($schoolId)) {
            $query->where('presences.school_id', (int) $schoolId);
        }

        // Classe obligatoire
        $classroomId = $request->input('classroom_id', $request->input('idClasse'));
        if (empty($classroomId)) {
            return response()->json([
                'success' => false,
                'message' => 'Le paramètre classroom_id est requis pour récupérer la liste des présences.',
            ], 422);
        }

        // Validation d'existence et d'appartenance à l'école
        $cls = Classroom::query()->find((int) $classroomId);
        if (! $cls) {
            return response()->json([
                'success' => false,
                'message' => 'Classe introuvable.',
            ], 404);
        }
        // Vérification d'appartenance à l'école via la logique de chainage :
        // On considère qu'une classe appartient à l'école si l'utilisateur a bien ce school_id
        if (! empty($schoolId) && (int) $schoolId !== (int) data_get(Auth::user(), 'school_id')) {
            return response()->json([
                'success' => false,
                'message' => "La classe n'appartient pas à l'école spécifiée.",
            ], 422);
        }
        $query->where('presences.classroom_id', (int) $classroomId);

        // Filtre optionnel par niveau académique
        if ($request->filled('academic_level_id')) {
            $query->where('presences.academic_level_id', (int) $request->academic_level_id);
        }

        // Date effective: paramètre ou aujourd'hui par défaut
        $effectiveDate = $request->input('date', now()->format('Y-m-d'));
        $query->whereDate('presences.created_at', $effectiveDate);

        // Nouveau: filtre optionnel par statut
        if ($request->filled('status')) {
            $status = mb_strtolower((string) $request->input('status'));
            switch ($status) {
                case 'present':
                    $query->where('presences.presence', true);
                    break;
                case 'absent':
                    // tous les absents (justifiés, malades ou non)
                    $query->where('presences.presence', false);
                    break;
                case 'absent_justified':
                    $query->where('presences.presence', false)
                        ->where('presences.absent_justified', true);
                    break;
                case 'sick':
                    $query->where('presences.presence', false)
                        ->where('presences.sick', true);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Status invalide. Valeurs acceptées: present, absent, absent_justified, sick.',
                    ], 422);
            }
        }

        // Recherche filtrante sur l’élève
        if ($request->filled('search')) {
            $search = mb_strtolower(mb_trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(students.name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(students.lastname) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(students.firstname) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(students.matricule) LIKE ?', ["%{$search}%"]);
            });
        }

        // Tri alphabétique par élève
        $query->orderBy('students.lastname')
            ->orderBy('students.firstname')
            ->orderBy('students.name');

        $presences = $query->get();

        return response()->json([
            'data' => PresenceRessource::collection($presences),
            'message' => 'Liste des présences de la classe récupérée avec succès.',
            'success' => true,
            'total' => $presences->count(),
            'classroom_id' => (int) $classroomId,
            'date' => $effectiveDate,
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'date' => ['nullable', 'date'],
        ]);

        $date = $request->input('date', now()->format('Y-m-d'));
        // Vérification que la date n'est pas dans le futur
        if (strtotime($date) > strtotime(now()->format('Y-m-d'))) {
            return response()->json([
                'success' => false,
                'message' => 'La date de présence ne peut pas être dans le futur.',
            ], 422);
        }

        // Vérifier que la classe appartient à l'école de l'utilisateur (si connue) ou à school_id fourni
        $classroom = Classroom::find($request->input('classroom_id'));
        if (! $classroom) {
            return response()->json(['message' => 'Classe introuvable'], 404);
        }
        // Vérification d'appartenance à l'école via la logique de chainage :
        $contextSchoolId = $request->input('school_id', data_get(Auth::user(), 'school_id'));
        if (! empty($contextSchoolId) && (int) $contextSchoolId !== (int) data_get(Auth::user(), 'school_id')) {
            return response()->json([
                'success' => false,
                'message' => "La classe n'appartient pas à l'école spécifiée.",
            ], 422);
        }

        $registrations = Registration::with(['student', 'school', 'classroom'])
            ->where('classroom_id', $classroom->id)
            ->where('registration_status', true)
            ->whereHas('student', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->get();

        if ($registrations->isEmpty()) {
            return response()->json([
                'message' => 'Aucun élève trouvé pour cette classe ',
            ], 404);
        }
        foreach ($registrations as $registration) {
            $student = $registration->student;
            // Vérifie si une présence existe déjà pour la date
            $existingPresence = Presence::where('student_id', $student->id)
                ->where('classroom_id', $registration->classroom_id)
                ->whereDate('created_at', $date)
                ->first();
            if ($existingPresence) {
                continue; // on saute pour éviter les doublons
            }
            // Crée la présence
            Presence::create([
                'student_id' => $student->id,
                'school_id' => $registration->school_id,
                'classroom_id' => $registration->classroom_id,
                'academic_level_id' => $registration->classroom->academic_level_id ?? null,
                'presence' => true,
                'absent_justified' => false,
                'sick' => false,
                'created_at' => $date,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Présences initialisées avec succès',
            'count' => $registrations->count(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $presence = Presence::with(['student', 'school', 'classroom'])->find($id);
        if (! $presence) {
            return response()->json([
                'message' => 'Présence non trouvée',
            ], 404);
        }

        return response()->json([
            'data' => $presence,
            'success' => true,
            'message' => 'Présence récupérée avec succès',
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        try {
            $presence = Presence::findOrFail($id);
            $request->validate([
                'presence' => ['required', 'boolean'],
                'sick' => ['nullable', 'boolean'],
                'absent_justified' => ['nullable', 'boolean'],
            ]);
            $presence->update([
                'presence' => $request->input('presence'),
                'sick' => $request->input('sick', $presence->sick),
                'absent_justified' => $request->input('absent_justified', $presence->absent_justified),
            ]);

            return response()->json([
                'data' => $presence,
                'success' => true,
                'message' => 'Présence mise à jour avec succès',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la présence',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Compat: Mise à jour d'une présence en lisant le l'ID et la valeur depuis le corps.
     * Attendu dans le body: { id: number, presence?: bool, present?: bool }
     * Retourne 422 si aucune des clés presence/present n'est fournie.
     */
    public function updateByBody(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:presences,id'],
            'presence' => ['nullable', 'boolean'],
            'present' => ['nullable', 'boolean'],
        ]);

        // Déterminer la valeur à appliquer
        $hasPresence = $request->exists('presence');
        $hasPresent = $request->exists('present');
        if (! $hasPresence && ! $hasPresent) {
            return response()->json([
                'success' => false,
                'message' => "Le corps doit contenir 'presence' ou 'present' (booléen).",
            ], 422);
        }
        $value = $hasPresence ? (bool) $request->boolean('presence') : (bool) $request->boolean('present');

        $presence = Presence::findOrFail((int) $validated['id']);
        $presence->update(['presence' => $value]);

        return response()->json([
            'data' => $presence->fresh(['student', 'classroom']),
            'success' => true,
            'message' => 'Présence mise à jour avec succès',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $presence = Presence::find($id);
        if (! $presence) {
            return response()->json([
                'message' => 'Présence non trouvée',
            ], 404);
        }

        $presence->delete();

        return response()->json([
            'success' => true,
            'message' => 'Présence supprimée avec succès',
        ], 200);
    }

    /**
     * Marquer la présence de plusieurs élèves.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // Ancienne méthode markMultiple supprimée: logique remplacée par updateStudentPresence / bulk endpoints.

    /**
     * Mettre à jour la présence d'un élève pour une date (par défaut aujourd'hui)
     */
    public function updateStudentPresence(Request $request, int $studentId)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        // Vérification que la date n'est pas dans le futur
        if (strtotime($date) > strtotime(now()->format('Y-m-d'))) {
            return response()->json([
                'success' => false,
                'message' => 'La date de présence ne peut pas être dans le futur.',
            ], 422);
        }

        // Validation optionnelle de classroom_id si fourni
        $request->validate([
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'present' => ['nullable', 'boolean'],
        ]);

        $present = (bool) $request->input('present', true);
        $student = Student::find($studentId);
        if (! $student) {
            return response()->json(['message' => 'Élève introuvable'], 404);
        }

        // Contexte d'école (pour validation éventuelle de la classe fournie)
        $contextSchoolId = $request->input('school_id', data_get(Auth::user(), 'school_id'));

        // Déterminer la classe: priorité au classroom_id fourni, sinon inscription active
        $classroomId = null;
        $schoolId = null;
        $filiereId = null;

        if ($request->filled('classroom_id')) {
            $cls = Classroom::query()->find((int) $request->input('classroom_id'));
            if (! $cls) {
                return response()->json(['message' => 'Classe introuvable'], 404);
            }
            $cls->loadMissing('filiaire.academicLevel.cycle');
            $clsSchoolId = $cls->filiaire?->academicLevel?->cycle?->school_id;
            if (! empty($contextSchoolId) && (int) $clsSchoolId !== (int) $contextSchoolId) {
                return response()->json([
                    'success' => false,
                    'message' => "La classe n'appartient pas à l'école spécifiée.",
                ], 422);
            }
            $classroomId = (int) $cls->id;
            $schoolId = $clsSchoolId ? (int) $clsSchoolId : null;
            $academicLevelId = $cls->academic_level_id ?? $cls->academicLevel?->id;
        } else {
            // Récupérer la classe depuis l'inscription active si possible
            $registration = Registration::query()
                ->with('classroom.filiaire.academicLevel.cycle')
                ->where('student_id', $student->id)
                ->where('registration_status', true)
                ->latest()
                ->first();

            $classroomId = $registration?->classroom_id;
            $schoolId = $registration?->school_id ?? ($registration?->classroom?->filiaire?->academicLevel?->cycle?->school_id ?? $student->school_id);
            $academicLevelId = $registration?->classroom?->academic_level_id;
        }

        // Si aucune classe déterminée, refuser la création
        if (empty($classroomId)) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d’enregistrer la présence sans classe. Veuillez spécifier classroom_id ou créer une inscription active.',
            ], 422);
        }

        $presence = Presence::where('student_id', $studentId)
            ->whereDate('created_at', $date)
            ->first();
        if (! $presence) {
            $presence = Presence::create([
                'student_id' => $student->id,
                'presence' => $present,
                'school_id' => $schoolId ?? $student->school_id,
                'classroom_id' => $classroomId,
                'academic_level_id' => $academicLevelId,
                'absent_justified' => false,
                'sick' => false,
                'created_at' => $date,
            ]);
        } else {
            // Mettre à jour la présence et, si nécessaire, corriger les métadonnées de classe/école/niveau
            $updates = ['presence' => $present];
            if ($classroomId && $presence->classroom_id !== $classroomId) {
                $updates['classroom_id'] = $classroomId;
            }
            if ($schoolId && $presence->school_id !== $schoolId) {
                $updates['school_id'] = $schoolId;
            }
            if ($academicLevelId && $presence->academic_level_id !== $academicLevelId) {
                $updates['academic_level_id'] = $academicLevelId;
            }
            if (! empty($updates)) {
                $presence->update($updates);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Présence mise à jour',
            'data' => $presence->fresh(['student', 'classroom']),
            'date' => $date,
        ]);
    }

    // Méthodes dailyList / dailyStudents fusionnées dans index() et
    // markStudentPresence fusionnée dans updateStudentPresence.

    /**
     * Enregistrer en masse les présences pour une date.
     */
    public function bulkStorePresences(Request $request, int $classroomId)
    {
        if (! Schema::hasColumn('presences', 'classroom_id')) {
            return response()->json([
                'message' => "Colonne 'classroom_id' manquante dans la table 'presences' pour la connection DB actuelle.",
            ], 500);
        }
        $date = $request->input('date', now()->format('Y-m-d'));
        $items = $request->input('presences', []); // [{student_id:1,present:true}, ...]
        if (empty($items)) {
            return response()->json(['message' => 'Aucune donnée fournie'], 400);
        }

        // Charger la classe pour déterminer la filière si nécessaire
        $classroom = Classroom::query()->find($classroomId);
        $academicLevelId = $classroom?->academic_level_id;

        $created = [];
        foreach ($items as $row) {
            if (! isset($row['student_id'])) {
                continue;
            }
            $student = Student::find($row['student_id']);
            if (! $student) {
                continue;
            }
            $present = (bool) ($row['present'] ?? true);

            $presence = Presence::firstOrCreate([
                'student_id' => $student->id,
                'school_id' => $student->school_id,
                'classroom_id' => $classroomId,
                'academic_level_id' => $academicLevelId,
                'created_at' => $date,
            ], [
                'presence' => $present,
                'absent_justified' => false,
                'sick' => false,
            ]);
            if ($presence->wasRecentlyCreated === false && $presence->presence !== $present) {
                $presence->update(['presence' => $present]);
            }
            $created[] = $presence->id;
        }

        return response()->json([
            'success' => true,
            'message' => 'Présences enregistrées',
            'ids' => $created,
            'date' => $date,
        ]);
    }

    /**
     * Export des présences au format Excel selon les mêmes filtres que index.
     * Query: date (YYYY-MM-DD), classroom_id ou idClasse, school_id
     */
    public function export(Request $request)
    {
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $classroomId = $request->input('classroom_id', $request->input('idClasse'));
        $schoolId = $request->input('school_id', data_get(Auth::user(), 'school_id'));

        // On ne valide plus l'appartenance classe/école via Classroom (le modèle n'a plus de school_id).

        return new PresencesExport(
            dateDebut: $dateDebut,
            dateFin: $dateFin,
            schoolId: $schoolId ? (int) $schoolId : null,
            classroomId: $classroomId ? (int) $classroomId : null,
        );
    }

    /**
     * Exportation des présences en PDF sans wrapper Laravel.
     */
    public function exportPdf(Request $request)
    {
        $query = Presence::with(['student', 'school', 'classroom'])
            ->leftJoin('students', 'students.id', '=', 'presences.student_id')
            ->select('presences.*');

        // Scoping école: school_id param ou école de l'utilisateur
        $schoolId = $request->input('school_id', data_get(Auth::user(), 'school_id'));
        if (! empty($schoolId)) {
            $query->where('presences.school_id', (int) $schoolId);
        }
        // Accepter classroom_id ou idClasse
        $classroomId = $request->input('classroom_id', $request->input('idClasse'));
        if (! empty($classroomId)) {
            // On filtre simplement par classroom_id sans revalider via Classroom::school_id
            $query->where('presences.classroom_id', (int) $classroomId);
        }

        // Aligner la logique de date avec l'export Excel: date_debut/date_fin (plage) ou aucune restriction si non fourni.
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $singleDate = $request->input('date');
        if ($singleDate && (! $dateDebut && ! $dateFin)) {
            $dateDebut = $singleDate;
            $dateFin = $singleDate;
        }

        if ($dateDebut && $dateFin) {
            $query->whereBetween('presences.created_at', [$dateDebut, $dateFin]);
        } elseif ($dateDebut) {
            $query->whereDate('presences.created_at', $dateDebut);
        }

        // Nouveau: filtre optionnel par statut aussi pour le PDF
        if ($request->filled('status')) {
            $status = mb_strtolower((string) $request->input('status'));
            switch ($status) {
                case 'present':
                    $query->where('presences.presence', true);
                    break;
                case 'absent':
                    $query->where('presences.presence', false);
                    break;
                case 'absent_justified':
                    $query->where('presences.presence', false)
                        ->where('presences.absent_justified', true);
                    break;
                case 'sick':
                    $query->where('presences.presence', false)
                        ->where('presences.sick', true);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Status invalide. Valeurs acceptées: present, absent, absent_justified, sick.',
                    ], 422);
            }
        }

        // Tri : du plus récent au plus ancien, puis alphabétique
        $query->orderBy('presences.created_at', 'desc')
        ->orderBy('students.lastname')
        ->orderBy('students.firstname')
        ->orderBy('students.name');


        $presences = $query->get();

        // Préparer les méta pour la vue et le nom de fichier
        $d1 = $dateDebut ?: now()->format('Y-m-d');
        $d2 = $dateFin ?: $d1;

        // Générer le HTML avec la vue Blade unifiée des exports
        $html = view('exports.presences', [
            'presences' => $presences,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
        ])->render();

        // Utiliser dompdf directement
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'presences_'.$d1.'_to_'.$d2.'.pdf';

        // Retourner le PDF en téléchargement
        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }
}
