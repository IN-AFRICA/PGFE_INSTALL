<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Person;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonPresenceRequest;
use App\Models\AcademicPersonal;
use App\Models\PersonPresence;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Exports\PersonPresencesExport;

final class PersonPresenceController extends Controller
{
    /**
     * Initialisation générale des présences de tous les academic_personals de l'école.
     * Query params: date (optionnel, défaut aujourd'hui)
     * School est pris implicitement depuis l'utilisateur connecté.
     */
    public function initializeAll(Request $request)
    {
        try {
            $user = Auth::user();
            $schoolId = (int) data_get($user, 'school_id');
            if (empty($schoolId)) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de déterminer l'école de l'utilisateur.",
                ], 422);
            }

            $date = $request->input('date', now()->format('Y-m-d'));

            // Vérification que la date n'est pas dans le futur
            if (strtotime($date) > strtotime(now()->format('Y-m-d'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'La date de présence ne peut pas être dans le futur.',
                ], 422);
            }

            $personnels = AcademicPersonal::query()
                ->where('school_id', $schoolId)
                ->get();

            if ($personnels->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun personnel académique trouvé pour cette école.',
                ], 404);
            }

            $createdCount = 0;

            foreach ($personnels as $personnel) {
                // Vérifie si une présence existe déjà pour la date
                $existingPresence = PersonPresence::query()
                    ->where('personnel_id', $personnel->id)
                    ->where('school_id', $schoolId)
                    ->whereDate('created_at', $date)
                    ->first();

                if ($existingPresence) {
                    continue; // on saute pour éviter les doublons
                }

                $created = PersonPresence::create([
                    'personnel_id' => $personnel->id,
                    'presence' => true,
                    'absent_justified' => false,
                    'sick' => false,
                    'school_id' => $schoolId,
                    'author_id' => (int) data_get($user, 'id'),
                ]);

                // Forcer created_at à la date donnée si différente d'aujourd'hui
                if ($date !== now()->format('Y-m-d')) {
                    $created->created_at = $date . ' 00:00:00';
                    $created->save();
                }

                $createdCount++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Présences des personnels académiques initialisées avec succès.',
                'count' => $createdCount,
                'date' => $date,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initialisation des présences.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Liste des présences du personnel pour une date (défaut: aujourd'hui), filtrées par école de l'utilisateur.
     * Query params:
     * - date: YYYY-MM-DD (optionnel, défaut aujourd'hui)
     * - school_id: optionnel (défaut: école de l'utilisateur)
     * - status: present | absent | absent_justified | sick (optionnel)
     * - search: filtre texte sur le personnel (nom, prénom, matricule, etc.)
     */
    public function index(Request $request)
    {
        try {
            $query = PersonPresence::with(['personnel', 'school']);

            // Scoping école: param ou école de l'utilisateur
            $schoolId = $request->input('school_id', data_get(Auth::user(), 'school_id'));
            if (! empty($schoolId)) {
                $query->where('person_presences.school_id', (int) $schoolId);
            }

            // Date effective
            $date = $request->input('date', now()->format('Y-m-d'));
            $query->whereDate('person_presences.created_at', $date);

            // Filtre par statut de présence (present, absent, absent_justified, sick)
            if ($request->filled('status')) {
                $status = mb_strtolower((string) $request->input('status'));
                switch ($status) {
                    case 'present':
                        $query->where('person_presences.presence', true);
                        break;
                    case 'absent':
                        // Absents non justifiés uniquement (ni justifiés, ni malades)
                        $query->where('person_presences.presence', false)
                            ->where('person_presences.absent_justified', false)
                            ->where('person_presences.sick', false);
                        break;
                    case 'absent_justified':
                        $query->where('person_presences.presence', false)
                            ->where('person_presences.absent_justified', true);
                        break;
                    case 'sick':
                        $query->where('person_presences.presence', false)
                            ->where('person_presences.sick', true);
                        break;
                    default:
                        return response()->json([
                            'success' => false,
                            'message' => 'Statut de présence invalide. Valeurs acceptées : present, absent, absent_justified, sick.',
                        ], 422);
                }
            }

            // Jointure sur les personnels pour permettre le tri et la recherche
            $query->leftJoin('academic_personals', 'academic_personals.id', '=', 'person_presences.personnel_id')
                ->select('person_presences.*');

            // Recherche sur le personnel (nom, post-nom, prénom, matricule, téléphone, email)
            if ($request->filled('search')) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('academic_personals.name', 'like', "%{$search}%")
                        ->orWhere('academic_personals.pre_name', 'like', "%{$search}%")
                        ->orWhere('academic_personals.post_name', 'like', "%{$search}%")
                        ->orWhere('academic_personals.matricule', 'like', "%{$search}%")
                        ->orWhere('academic_personals.phone', 'like', "%{$search}%")
                        ->orWhere('academic_personals.email', 'like', "%{$search}%");
                });
            }

            // Tri par nom du personnel si disponible
            $query->orderBy('academic_personals.name');

            $presences = $query->get();

            if ($presences->isEmpty()) {
                return response()->json([
                    'data' => $presences,
                    'success' => true,
                    'message' => 'Aucune présence trouvée pour les critères fournis.',
                ]);
            }

            return response()->json([
                'data' => $presences,
                'success' => true,
                'message' => 'Liste des présences du personnel récupérée avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des présences.',
            ], 500);
        }
    }

    public function store(PersonPresenceRequest $request)
    {
        try {
            $user = Auth::user();
            $schoolId = (int) $request->input('school_id', data_get($user, 'school_id'));
            if (empty($schoolId)) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de déterminer l'école (school_id).",
                ], 422);
            }

            $date = $request->input('date', now()->format('Y-m-d'));

            // Initialisation générale: créer les présences pour tous les personnels académiques de l'école
            if ($request->boolean('initialize_all')) {
                $personnels = AcademicPersonal::query()
                    ->where('school_id', $schoolId)
                    ->get();

                if ($personnels->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aucun personnel académique trouvé pour cette école.',
                    ], 404);
                }

                $createdCount = 0;

                foreach ($personnels as $personnel) {
                    $existing = PersonPresence::query()
                        ->where('personnel_id', $personnel->id)
                        ->where('school_id', $schoolId)
                        ->whereDate('created_at', $date)
                        ->first();

                    if ($existing) {
                        continue;
                    }

                    $created = PersonPresence::create([
                        'personnel_id' => $personnel->id,
                        'presence' => true,
                        'absent_justified' => false,
                        'sick' => false,
                        'school_id' => $schoolId,
                        'author_id' => (int) data_get($user, 'id'),
                    ]);

                    if ($date !== now()->format('Y-m-d')) {
                        $created->created_at = $date.' 00:00:00';
                        $created->save();
                    }

                    $createdCount++;
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Présences des personnels académiques initialisées avec succès.',
                    'count' => $createdCount,
                    'date' => $date,
                ], 201);
            }

            // Cas simple: création / mise à jour d'une seule présence
            $request->merge([
                'school_id' => $schoolId,
                'author_id' => $request->input('author_id', data_get($user, 'id')),
            ]);

            $validated = $request->validated();
            $presence = PersonPresence::create($validated);

            if ($date !== now()->format('Y-m-d')) {
                $presence->created_at = $date.' 00:00:00';
                $presence->save();
            }

            return response()->json([
                'data' => $presence,
                'success' => true,
                'message' => 'Présence créée avec succès.',
                'date' => $date,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la présence.'.$e->getMessage(),
            ], 422);
        }

    }

    public function show(PersonPresence $personPresence)
    {
        try {
            $personPresence = PersonPresence::findOrFail($personPresence->id);

            return response()->json([
                'data' => $personPresence,
                'success' => true,
                'message' => 'Présence récupérée avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Présence non trouvée.'.$e->getMessage(),
            ], 404);
        }

    }

    public function update(PersonPresenceRequest $request, PersonPresence $personPresence)
    {
        try {
            $personPresence = PersonPresence::findOrFail($personPresence->id);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Présence non trouvée.'.$e->getMessage(),
            ], 404);
        }
        $validated = $request->validated();
        $personPresence->update($validated);

        return response()->json([
            'data' => $personPresence,
            'success' => true,
            'message' => 'Présence mise à jour avec succès.',
        ]);
    }

    public function destroy(PersonPresence $personPresence)
    {
        $personPresence->delete();

        return response()->noContent();
    }

    /**
     * Mise à jour (ou création) de la présence d'un personnel pour une date.
     * Body: presence (bool), date (YYYY-MM-DD, facultatif défaut aujourd'hui)
     */
    public function updateByPersonnel(Request $request, int $personnelId)
    {
        $request->validate([
            'presence' => 'required|boolean',
            'date' => 'nullable|date',
        ]);

        $user = Auth::user();
        $schoolId = (int) ($request->input('school_id', data_get($user, 'school_id')));
        if (empty($schoolId)) {
            return response()->json([
                'success' => false,
                'message' => "Impossible de déterminer l'école (school_id).",
            ], 422);
        }
        $date = $request->input('date', now()->format('Y-m-d'));

        // Trouver une présence existante pour ce personnel/date/école
        $existing = PersonPresence::query()
            ->where('personnel_id', (int) $personnelId)
            ->where('school_id', $schoolId)
            ->whereDate('created_at', $date)
            ->first();

        if ($existing) {
            $existing->update([
                'presence' => (bool) $request->boolean('presence'),
                'author_id' => data_get($user, 'id'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Présence mise à jour.',
                'data' => $existing->fresh(),
                'date' => $date,
            ], Response::HTTP_OK);
        }

        $created = PersonPresence::create([
            'personnel_id' => (int) $personnelId,
            'presence' => (bool) $request->boolean('presence'),
            'school_id' => $schoolId,
            'author_id' => (int) data_get($user, 'id'),
        ]);

        // Forcer created_at à la date donnée si différente d'aujourd'hui
        if ($date !== now()->format('Y-m-d')) {
            $created->created_at = $date.' 00:00:00';
            $created->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Présence créée.',
            'data' => $created,
            'date' => $date,
        ], Response::HTTP_CREATED);
    }

    /**
     * Mise à jour via corps de requête: attend personnel_id et presence (+date optionnelle)
     */
    public function updateByBody(Request $request)
    {
        $request->validate([
            'personnel_id' => 'required|integer|exists:academic_personals,id',
            'presence' => 'required|boolean',
            'date' => 'nullable|date',
        ]);

        return $this->updateByPersonnel($request, (int) $request->input('personnel_id'));
    }

    /**
     * Enregistrement en masse d'une liste de présences du personnel
     * Body: { items: [ { personnel_id, presence, date? }, ... ] }
     */
    public function bulkStore(Request $request)
    {
        $items = $request->input('items');
        if (! is_array($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Le champ items (array) est requis.',
            ], 422);
        }

        $user = Auth::user();
        $schoolId = (int) ($request->input('school_id', data_get($user, 'school_id')));
        if (empty($schoolId)) {
            return response()->json([
                'success' => false,
                'message' => "Impossible de déterminer l'école (school_id).",
            ], 422);
        }

        $results = [];
        foreach ($items as $row) {
            $personnelId = (int) data_get($row, 'personnel_id');
            $presence = filter_var(data_get($row, 'presence'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $date = data_get($row, 'date', now()->format('Y-m-d'));
            if (! $personnelId || $presence === null) {
                continue;
            }

            $existing = PersonPresence::query()
                ->where('personnel_id', $personnelId)
                ->where('school_id', $schoolId)
                ->whereDate('created_at', $date)
                ->first();

            if ($existing) {
                $existing->update([
                    'presence' => (bool) $presence,
                    'author_id' => (int) data_get($user, 'id'),
                ]);
                $results[] = ['id' => $existing->id, 'status' => 'updated'];
            } else {
                $created = PersonPresence::create([
                    'personnel_id' => $personnelId,
                    'presence' => (bool) $presence,
                    'school_id' => $schoolId,
                    'author_id' => (int) data_get($user, 'id'),
                ]);
                if ($date !== now()->format('Y-m-d')) {
                    $created->created_at = $date.' 00:00:00';
                    $created->save();
                }
                $results[] = ['id' => $created->id, 'status' => 'created'];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Présences enregistrées',
            'results' => $results,
        ], Response::HTTP_OK);
    }

    /**
     * Export Excel des présences du personnel
     * Query: date_debut, date_fin, school_id
     */
    public function export(Request $request)
    {
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $schoolId = $request->input('school_id', data_get(Auth::user(), 'school_id'));

        return new PersonPresencesExport(
            dateDebut: $dateDebut,
            dateFin: $dateFin,
            schoolId: $schoolId ? (int) $schoolId : null,
        );
    }

    /**
     * Export PDF des présences du personnel
     */
    public function exportPdf(Request $request)
    {
        $query = PersonPresence::with(['personnel', 'school'])
            ->leftJoin('academic_personals', 'academic_personals.id', '=', 'person_presences.personnel_id')
            ->select('person_presences.*');

        $schoolId = $request->input('school_id', data_get(Auth::user(), 'school_id'));
        if (! empty($schoolId)) {
            $query->where('person_presences.school_id', (int) $schoolId);
        }

        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $singleDate = $request->input('date');
        if ($singleDate && (! $dateDebut && ! $dateFin)) {
            $dateDebut = $singleDate;
            $dateFin = $singleDate;
        }
        if ($dateDebut && $dateFin) {
            $query->whereBetween('person_presences.created_at', [$dateDebut, $dateFin]);
        } elseif ($dateDebut) {
            $query->whereDate('person_presences.created_at', $dateDebut);
        }

        if ($request->filled('status')) {
            $status = mb_strtolower((string) $request->input('status'));
            if ($status === 'present') {
                $query->where('person_presences.presence', true);
            } elseif ($status === 'absent') {
                $query->where('person_presences.presence', false);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Status invalide. Valeurs acceptées: present, absent.',
                ], 422);
            }
        }

        $query->orderBy('person_presences.created_at', 'desc')
            ->orderBy('academic_personals.name');

        $rows = $query->get();

        $d1 = $dateDebut ?: now()->format('Y-m-d');
        $d2 = $dateFin ?: $d1;

        $html = view('exports.person_presences', [
            'presences' => $rows,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'person_presences_'.$d1.'_to_'.$d2.'.pdf';

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }
}
