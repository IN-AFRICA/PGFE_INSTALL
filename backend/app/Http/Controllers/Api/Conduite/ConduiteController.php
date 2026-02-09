<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Conduite;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConduiteRequest;
use App\Models\Conduite;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ConduiteController extends Controller
{
    public function index(Request $request)
    {
        $query = Conduite::query();

        $search = strtolower(trim((string) $request->input('search', '')));

        if ($search !== '') {
            $query->whereRaw('LOWER(label) LIKE ?', ["%{$search}%"]);
        }

        $conduites = $query->orderBy('label')->get();

        return response()->json([
            'data' => $conduites,
            'message' => 'Liste des conduites récupérée avec succès',
            'success' => true,
        ]);
    }

    public function store(ConduiteRequest $request)
    {
        try {
            // Logic to create a new Conduite record
            $validated = $request->validated();

            $conduite = Conduite::create($validated);

            return response()->json([
                'data' => $conduite,
                'message' => 'Conduite créée avec succès',
                'success' => true,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function show($id)
    {
        try {
            // Logic to show a specific Conduite record
            $conduite = Conduite::findOrFail($id);

            return response()->json([
                'data' => $conduite,
                'message' => 'Conduite récupérée avec succès',
                'success' => true,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }

    }

    public function update(ConduiteRequest $request, $id)
    {
        try {
            $conduite = Conduite::findOrFail($id);
            if (! $conduite) {
                return response()->json([
                    'message' => 'Conduite non trouvée',
                    'success' => false,
                ], Response::HTTP_NOT_FOUND);
            }
            $validated = $request->validated();

            $conduite = Conduite::findOrFail($id);
            $conduite->update($validated);

            return response()->json([
                'data' => $conduite,
                'message' => 'Conduite mise à jour avec succès',
                'success' => true,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function destroy($id)
    {
        try {
            $conduite = Conduite::findOrFail($id);
            if (! $conduite) {
                return response()->json([
                    'message' => 'Conduite non trouvée',
                    'success' => false,
                ], Response::HTTP_NOT_FOUND);
            }
            // Logic to delete a specific Conduite record
            $conduite = Conduite::findOrFail($id);
            $conduite->delete();

            return response()->json([
                'message' => 'Conduite supprimée avec succès',
                'success' => true,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }

    }
}
