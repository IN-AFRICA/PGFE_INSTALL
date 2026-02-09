<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Formation;

use App\Http\Controllers\Controller;
use App\Http\Requests\FormationContinueRequest;
use App\Models\FormationContinue;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class FormationContinueController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = FormationContinue::with(['academicPersonal', 'school', 'classroom', 'filiere']);

            // Filtres optionnels
            if ($request->filled('school_id') && is_numeric($request->input('school_id'))) {
                $query->where('school_id', (int) $request->input('school_id'));
            }
            if ($request->filled('classroom_id') && is_numeric($request->input('classroom_id'))) {
                $query->where('classroom_id', (int) $request->input('classroom_id'));
            }
            if ($request->filled('filiere_id') && is_numeric($request->input('filiere_id'))) {
                $query->where('filiere_id', (int) $request->input('filiere_id'));
            }
            if ($request->filled('academic_personal_id') && is_numeric($request->input('academic_personal_id'))) {
                $query->where('academic_personal_id', (int) $request->input('academic_personal_id'));
            }
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
                });
            }
            if ($request->filled('date_from')) {
                $query->whereDate('start_date', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('end_date', '<=', $request->input('date_to'));
            }

            $perPage = (int) $request->input('per_page', 20);
            $items = $query->latest('id')->paginate($perPage);

            return response()->json([
                'data' => $items,
                'success' => true,
                'message' => 'Liste des formations continues.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des formations continues: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(FormationContinueRequest $request)
    {
        try {
            $user = $request->user();
            $data = $request->validated();
            $data['school_id'] = $user->school_id;
            $formation = FormationContinue::create($data);

            return response()->json([
                'data' => $formation->load(['academicPersonal', 'school', 'classroom', 'filiere']),
                'success' => true,
                'message' => 'Formation continue créée avec succès.',
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la formation: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(FormationContinue $formationContinue)
    {
        $formationContinue->load(['academicPersonal', 'school', 'classroom', 'filiere']);
        $data = $formationContinue->toArray();
        $data['school_year_id'] = $formationContinue->school_year_id;
        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => 'Détail de la formation continue.',
        ], Response::HTTP_OK);
    }

    public function update(FormationContinueRequest $request, FormationContinue $formationContinue)
    {
        try {
            $formationContinue->update($request->validated());

            return response()->json([
                'data' => $formationContinue->load(['academicPersonal', 'school', 'classroom', 'filiere']),
                'success' => true,
                'message' => 'Formation continue mise à jour avec succès.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la formation: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(FormationContinue $formationContinue)
    {
        try {
            $formationContinue->delete();

            return response()->json([
                'success' => true,
                'message' => 'Formation continue supprimée avec succès.',
            ], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la formation: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

