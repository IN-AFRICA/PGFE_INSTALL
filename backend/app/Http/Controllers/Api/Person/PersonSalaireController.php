<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Person;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonSalaireRequest;
use App\Http\Resources\PersonSalaireResource;
use App\Models\PersonSalaire;
use App\Models\SchoolYear;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PersonSalaireController extends Controller
{
    public function index(Request $request): JsonResponse|null|Exception
    {
        try {
            $query = PersonSalaire::with(['academicPersonal'])
                ->whereNotNull('academic_personal_id');

            // Filtres optionnels
            if ($request->filled('academic_personal_id') && is_numeric($request->input('academic_personal_id'))) {
                $query->where('academic_personal_id', (int) $request->input('academic_personal_id'));
            }
            if ($request->filled('school_year_id') && is_numeric($request->input('school_year_id'))) {
                $query->where('school_year_id', (int) $request->input('school_year_id'));
            }
            if ($request->filled('mois_id') && is_numeric($request->input('mois_id'))) {
                $query->where('mois_id', (int) $request->input('mois_id'));
            }
            if ($request->filled('montant_min') && is_numeric($request->input('montant_min'))) {
                $query->where('montant', '>=', (float) $request->input('montant_min'));
            }
            if ($request->filled('montant_max') && is_numeric($request->input('montant_max'))) {
                $query->where('montant', '<=', (float) $request->input('montant_max'));
            }

            $perPage = (int) $request->input('per_page', 20);
            $salaires = $query->latest()->paginate($perPage);

            if ($salaires->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'success' => true,
                    'message' => 'Aucun salaire trouvé.',
                ], Response::HTTP_OK);
            }

            return response()->json([
                'data' => PersonSalaireResource::collection($salaires),
                'success' => true,
                'message' => 'Liste des Salaires',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'Erreur lors de la récupération.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Statistiques des salaires par genre de personnel.
     *
     * GET /api/v1/hr/person-salaires/stats-by-gender
     * Optionnel: ?school_year_id=ID (par défaut: année scolaire active)
     */
    public function statsByGender(Request $request): JsonResponse
    {
        // Déterminer l'année scolaire cible
        $schoolYearId = $request->input('school_year_id');
        if (! $schoolYearId && method_exists(SchoolYear::class, 'active')) {
            $active = SchoolYear::active();
            $schoolYearId = $active?->id;
        }

        $query = PersonSalaire::query()
            ->selectRaw('academic_personals.gender as gender, SUM(person_salaires.montant) as total_salary, COUNT(DISTINCT person_salaires.academic_personal_id) as personnel_count')
            ->join('academic_personals', 'academic_personals.id', '=', 'person_salaires.academic_personal_id')
            ->whereNotNull('person_salaires.academic_personal_id')
            ->groupBy('academic_personals.gender');

        if ($schoolYearId) {
            $query->where('person_salaires.school_year_id', (int) $schoolYearId);
        }

        $stats = $query->get()->map(function ($row) {
            return [
                'gender' => $row->gender,
                'total_salary' => (float) $row->total_salary,
                'personnel_count' => (int) $row->personnel_count,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Statistiques des salaires par genre de personnel',
            'data' => $stats,
        ], Response::HTTP_OK);
    }

    public function store(PersonSalaireRequest $request): PersonSalaireResource|\Illuminate\Http\JsonResponse
    {
        try {
            $personSalaire = PersonSalaire::create($request->validated());

            return response()->json([
                'data' => new PersonSalaireResource($personSalaire),
                'success' => true,
                'message' => 'Salaire créé avec succès.',
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['message' => 'Erreur lors de la création.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(PersonSalaire $personSalaire)
    {
        try {
            if (! $personSalaire) {
                return response()->json([
                    'data' => null,
                    'success' => false,
                    'message' => 'Salaire non trouvé.',
                ], Response::HTTP_NOT_FOUND);
            }

            $salaire = PersonSalaire::with(['academicPersonal'])->findOrFail($personSalaire->id);
            return response()->json([
                'data' => new PersonSalaireResource($salaire),
                'success' => true,
                'message' => 'Détail du Salaire de l\'employé.',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'data' => null,
                'success' => false,
                'message' => 'Erreur lors de la récupération.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update(PersonSalaireRequest $request, PersonSalaire $personSalaire)
    {
        try {
            $personSalaire->update($request->validated());

            return response()->json([
                'data' => new PersonSalaireResource($personSalaire),
                'success' => true,
                'message' => 'Salaire mis à jour avec succès.',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['message' => 'Erreur lors de la mise à jour.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(PersonSalaire $personSalaire): \Illuminate\Http\JsonResponse
    {
        try {
            $personSalaire->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
