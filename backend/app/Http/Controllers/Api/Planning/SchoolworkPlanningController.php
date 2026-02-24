<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Planning;

use App\Http\Controllers\Controller;
use App\Models\PlanningFile;
use App\Models\SchoolworkPlanning;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SchoolworkPlanningController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = SchoolworkPlanning::with(['classroom', 'course', 'file', 'school', 'author']);

            if ($request->filled('school_id')) {
                $query->where('school_id', (int) $request->input('school_id'));
            }

            if ($request->filled('classroom_id')) {
                $query->where('classroom_id', (int) $request->input('classroom_id'));
            }

            if ($request->filled('course_id')) {
                $query->where('course_id', (int) $request->input('course_id'));
            }

            if ($search = $request->input('search')) {
                $query->where('label', 'like', "%{$search}%");
            }

            $perPage = (int) $request->input('per_page', 20);

            return response()->json([
                'success' => true,
                'data' => $query->latest('id')->paginate($perPage),
                'message' => 'Liste des planifications de travaux scolaires.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des planifications: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'file_id' => ['nullable', 'integer', 'exists:planning_files,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        try {
            if (! $user || ! $user->academicPersonal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun personnel académique n\'est lié à ce compte. Impossible d\'enregistrer l\'auteur de la planification.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (! $user->school_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune école n\'est associée à ce compte. Impossible d\'enregistrer la planification.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // L'école et l'auteur doivent être dérivés de l'utilisateur connecté
            $data['school_id'] = $user->school_id;
            $data['author_id'] = $user->academicPersonal->id;

            $planning = SchoolworkPlanning::create($data);

            return response()->json([
                'success' => true,
                'data' => $planning->load(['classroom', 'course', 'file', 'school', 'author']),
                'message' => 'Planification créée avec succès.',
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la planification: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(SchoolworkPlanning $schoolworkPlanning): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $schoolworkPlanning->load(['classroom', 'course', 'file', 'school', 'author']),
            'message' => 'Détail de la planification.',
        ], Response::HTTP_OK);
    }

    public function update(Request $request, SchoolworkPlanning $schoolworkPlanning): JsonResponse
    {
        $data = $request->validate([
            'label' => ['sometimes', 'string', 'max:255'],
            'classroom_id' => ['sometimes', 'integer', 'exists:classrooms,id'],
            'course_id' => ['sometimes', 'integer', 'exists:courses,id'],
            'file_id' => ['sometimes', 'nullable', 'integer', 'exists:planning_files,id'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
        ]);

        try {
            $schoolworkPlanning->update($data);

            return response()->json([
                'success' => true,
                'data' => $schoolworkPlanning->load(['classroom', 'course', 'file', 'school', 'author']),
                'message' => 'Planification mise à jour avec succès.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la planification: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(SchoolworkPlanning $schoolworkPlanning): JsonResponse
    {
        try {
            $schoolworkPlanning->delete();

            return response()->json([
                'success' => true,
                'message' => 'Planification supprimée avec succès.',
            ], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la planification: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
