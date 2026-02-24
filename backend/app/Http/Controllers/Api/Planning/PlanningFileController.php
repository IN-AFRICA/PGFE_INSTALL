<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Planning;

use App\Http\Controllers\Controller;
use App\Models\PlanningFile;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PlanningFileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PlanningFile::with(['classroom', 'school', 'author']);

            if ($request->filled('school_id')) {
                $query->where('school_id', (int) $request->input('school_id'));
            }

            if ($request->filled('classroom_id')) {
                $query->where('classroom_id', (int) $request->input('classroom_id'));
            }

            $perPage = (int) $request->input('per_page', 20);

            return response()->json([
                'success' => true,
                'data' => $query->latest('id')->paginate($perPage),
                'message' => 'Liste des fichiers de planification.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des fichiers: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'file_path' => ['required', 'string', 'max:1024'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
        ]);

        try {
            if (! $user || ! $user->academicPersonal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun personnel académique n\'est lié à ce compte. Impossible d\'enregistrer l\'auteur du fichier de planification.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (! $user->school_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune école n\'est associée à ce compte. Impossible d\'enregistrer le fichier de planification.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data['school_id'] = $user->school_id;
            $data['author_id'] = $user->academicPersonal->id;

            $file = PlanningFile::create($data);

            return response()->json([
                'success' => true,
                'data' => $file->load(['classroom', 'school', 'author']),
                'message' => 'Fichier de planification créé avec succès.',
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du fichier: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(PlanningFile $planningFile): JsonResponse
    {
        try {
            $planningFile->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fichier supprimé avec succès.',
            ], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du fichier: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
