<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Infrastructure;

use App\Http\Controllers\Controller;
use App\Models\InfraInfrastructureInventaire;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InfraInfrastructureInventaireItemV2Controller extends Controller
{
    /**
     * GET: Liste des items (observations) d'un inventaire d'infrastructure
     */
    public function index($id)
    {
        $inventaire = InfraInfrastructureInventaire::findOrFail($id);
        return response()->json([
            'data' => $inventaire->observations ?? [],
            'success' => true
        ]);
    }

    /**
     * Ajoute un ou plusieurs items (détails) à l'inventaire d'infrastructure (champ observations).
     * POST /api/v1/infrastructures/infrastructure-inventaires/{id}/item
     */
    public function store(Request $request, $id)
    {
        $inventaire = InfraInfrastructureInventaire::findOrFail($id);
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|string',
            'items.*.etat' => 'required|string',
            'items.*.label' => 'nullable|string',
            'items.*.description' => 'nullable|string',
        ]);

        $observations = $inventaire->observations ?? [];
        foreach ($data['items'] as $item) {
            $observations[] = $item;
        }
        $inventaire->observations = $observations;
        $inventaire->save();
        $inventaire->refresh();

        return response()->json([
            'data' => $inventaire->observations,
            'success' => true,
            'message' => count($data['items']) . ' item(s) ajouté(s) à l\'inventaire',
        ], Response::HTTP_CREATED);
    }
}
