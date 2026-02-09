<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accountings\Accounts\ClassComptabilityRequest;
use App\Models\ClassComptability;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ClassComptabilityController extends Controller
{
    /**
     * Afficher la liste paginée des classes comptables.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = ClassComptability::query();
        if ($user && ! $user->hasRole('super-admin')) {
            $query->where('school_id', $user->school_id);
        }
        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        }
        $classes = $query->latest()->paginate(10);
        if ($classes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune classe comptable trouvée.',
                'data' => [],
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => true,
            'message' => 'Liste des classes comptables récupérée avec succès.',
            'data' => $classes->items(),
            'meta' => [
                'current_page' => $classes->currentPage(),
                'last_page' => $classes->lastPage(),
                'per_page' => $classes->perPage(),
                'total' => $classes->total(),
                'next_page_url' => $classes->nextPageUrl(),
                'prev_page_url' => $classes->previousPageUrl(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Enregistrer une nouvelle classe comptable.
     */
    public function store(ClassComptabilityRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = $request->user();
            $data['user_id'] = $user->id;
            $data['school_id'] = $user->school_id;
            $class = ClassComptability::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Classe comptable créée avec succès.',
                'data' => $class,
            ], Response::HTTP_CREATED);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Afficher une classe comptable spécifique.
     */
    public function show(ClassComptability $classComptability): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Classe comptable récupérée avec succès.',
            'data' => $classComptability,
        ], Response::HTTP_OK);
    }

    /**
     * Mettre à jour une classe comptable.
     */
    public function update(ClassComptabilityRequest $request, ClassComptability $classComptability): JsonResponse
    {
        try {
            $data = $request->validated();
            $classComptability->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Classe comptable mise à jour avec succès.',
                'data' => $classComptability,
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Supprimer une classe comptable.
     */
    public function destroy(ClassComptability $classComptability): JsonResponse
    {
        try {
            $classComptability->delete();

            return response()->json([
                'success' => true,
                'message' => 'Classe comptable supprimée avec succès.',
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
