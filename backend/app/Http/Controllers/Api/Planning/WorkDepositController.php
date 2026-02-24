<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Planning;

use App\Http\Controllers\Controller;
use App\Models\WorkDeposit;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class WorkDepositController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = WorkDeposit::with(['schoolworkPlanning', 'file', 'classroom', 'student', 'school', 'author']);

            if ($request->filled('schoolwork_planning_id')) {
                $query->where('schoolwork_planning_id', (int) $request->input('schoolwork_planning_id'));
            }

            if ($request->filled('classroom_id')) {
                $query->where('classroom_id', (int) $request->input('classroom_id'));
            }

            if ($request->filled('student_id')) {
                $query->where('student_id', (int) $request->input('student_id'));
            }

            $perPage = (int) $request->input('per_page', 20);

            return response()->json([
                'success' => true,
                'data' => $query->latest('id')->paginate($perPage),
                'message' => 'Liste des dépôts de travaux.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des dépôts: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'schoolwork_planning_id' => ['required', 'integer', 'exists:schoolwork_plannings,id'],
            'file_id' => ['nullable', 'integer', 'exists:planning_files,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        try {
            if (! $user || ! $user->academicPersonal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun personnel académique n\'est lié à ce compte. Impossible d\'enregistrer l\'auteur du dépôt.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (! $user->school_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune école n\'est associée à ce compte. Impossible d\'enregistrer le dépôt.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data['school_id'] = $user->school_id;
            $data['author_id'] = $user->academicPersonal->id;

            $deposit = WorkDeposit::create($data);

            return response()->json([
                'success' => true,
                'data' => $deposit->load(['schoolworkPlanning', 'file', 'classroom', 'student', 'school', 'author']),
                'message' => 'Dépôt de travail enregistré avec succès.',
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du dépôt: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(WorkDeposit $workDeposit): JsonResponse
    {
        try {
            $workDeposit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Dépôt supprimé avec succès.',
            ], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du dépôt: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
