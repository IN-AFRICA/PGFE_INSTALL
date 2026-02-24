<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Activities;

use App\Http\Controllers\Controller;
use App\Models\SchoolActivity;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SchoolActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = SchoolActivity::with(['school', 'author']);

            if ($request->filled('school_id')) {
                $query->where('school_id', (int) $request->input('school_id'));
            }

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('label', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('place', 'like', "%{$search}%");
                });
            }

            $perPage = (int) $request->input('per_page', 20);

            return response()->json([
                'success' => true,
                'data' => $query->latest('id')->paginate($perPage),
                'message' => 'Liste des activités scolaires.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des activités: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'place' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        try {
            if (! $user || ! $user->academicPersonal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun personnel académique n\'est lié à ce compte. Impossible d\'enregistrer l\'auteur de l\'activité.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (! $user->school_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune école n\'est associée à ce compte. Impossible d\'enregistrer l\'activité.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data['school_id'] = $user->school_id;
            $data['author_id'] = $user->academicPersonal->id;

            $activity = SchoolActivity::create($data);

            return response()->json([
                'success' => true,
                'data' => $activity->load(['school', 'author']),
                'message' => 'Activité scolaire créée avec succès.',
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'activité: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(SchoolActivity $schoolActivity): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $schoolActivity->load(['school', 'author', 'studentActivities']),
            'message' => 'Détail de l\'activité scolaire.',
        ], Response::HTTP_OK);
    }

    public function update(Request $request, SchoolActivity $schoolActivity): JsonResponse
    {
        $data = $request->validate([
            'label' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'place' => ['sometimes', 'nullable', 'string', 'max:255'],
            'quantity' => ['sometimes', 'nullable', 'numeric'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
        ]);

        try {
            $schoolActivity->update($data);

            return response()->json([
                'success' => true,
                'data' => $schoolActivity->load(['school', 'author']),
                'message' => 'Activité scolaire mise à jour avec succès.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'activité: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(SchoolActivity $schoolActivity): JsonResponse
    {
        try {
            $schoolActivity->delete();

            return response()->json([
                'success' => true,
                'message' => 'Activité scolaire supprimée avec succès.',
            ], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'activité: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
