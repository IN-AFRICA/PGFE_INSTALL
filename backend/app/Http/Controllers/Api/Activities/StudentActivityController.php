<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Activities;

use App\Http\Controllers\Controller;
use App\Models\StudentActivity;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class StudentActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = StudentActivity::with(['schoolActivity', 'classroom', 'school', 'author']);

            if ($request->filled('school_activity_id')) {
                $query->where('school_activity_id', (int) $request->input('school_activity_id'));
            }

            if ($request->filled('classroom_id')) {
                $query->where('classroom_id', (int) $request->input('classroom_id'));
            }

            if ($request->filled('school_id')) {
                $query->where('school_id', (int) $request->input('school_id'));
            }

            $perPage = (int) $request->input('per_page', 20);

            return response()->json([
                'success' => true,
                'data' => $query->latest('id')->paginate($perPage),
                'message' => 'Liste des inscriptions des élèves aux activités.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des participations: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'school_activity_id' => ['required', 'integer', 'exists:school_activities,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
        ]);

        try {
            if (! $user || ! $user->academicPersonal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun personnel académique n\'est lié à ce compte. Impossible d\'enregistrer l\'auteur de la participation.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (! $user->school_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune école n\'est associée à ce compte. Impossible d\'enregistrer la participation.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data['school_id'] = $user->school_id;
            $data['author_id'] = $user->academicPersonal->id;

            $participation = StudentActivity::create($data);

            return response()->json([
                'success' => true,
                'data' => $participation->load(['schoolActivity', 'classroom', 'school', 'author']),
                'message' => 'Participation enregistrée avec succès.',
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la participation: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(StudentActivity $studentActivity): JsonResponse
    {
        try {
            $studentActivity->delete();

            return response()->json([
                'success' => true,
                'message' => 'Participation supprimée avec succès.',
            ], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la participation: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
