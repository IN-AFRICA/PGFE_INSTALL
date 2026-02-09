<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Models\CategoryComptability;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class CategoryComptabilityController extends Controller
{
    /**
     * Liste paginée des catégories de comptabilité
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = CategoryComptability::query();
        if ($user && ! $user->hasRole('super-admin')) {
            $query->where('school_id', $user->school_id);
        }
        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
        }
        try {
            $categories = $query->latest()->paginate(10);
            return response()->json([
                'message' => $categories->isEmpty() ? 'Aucune catégorie de comptabilité disponible' : 'Liste des catégories récupérée avec succès',
                'data' => $categories,
                'success' => true,
                'meta' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                    'next_page_url' => $categories->nextPageUrl(),
                    'prev_page_url' => $categories->previousPageUrl(),
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Création d'une catégorie
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:category_comptability,name',
        ]);
        $validated['user_id'] = $user->id;
        $validated['school_id'] = $user->school_id;
        try {
            $category = CategoryComptability::create($validated);

            return response()->json([
                'message' => 'Catégorie créée avec succès',
                'data' => $category,
                'success' => true,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création : '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mise à jour d'une catégorie
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $category = CategoryComptability::find($id);

            if (! $category) {
                return response()->json([
                    'message' => 'Catégorie non trouvée',
                    'success' => false,
                ], Response::HTTP_NOT_FOUND);
            }

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:category_comptability,name,'.$id,
                'description' => 'nullable|string',
            ]);

            $category->update($validated);

            return response()->json([
                'message' => 'Catégorie mise à jour avec succès',
                'data' => $category,
                'success' => true,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour : '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Suppression d'une catégorie
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $category = CategoryComptability::find($id);

            if (! $category) {
                return response()->json([
                    'message' => 'Catégorie non trouvée',
                    'success' => false,
                ], Response::HTTP_NOT_FOUND);
            }

            $category->delete();

            return response()->json([
                'message' => 'Catégorie supprimée avec succès',
                'success' => true,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression : '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
