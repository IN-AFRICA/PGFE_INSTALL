<?php

namespace App\Http\Controllers;

use App\Models\InfraInventory;
use App\Models\InfraInventoryEquipment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InfraInventoryItemController extends Controller
{
    /**
     * GET: Liste des items (équipements) d'un inventaire d'équipement
     */
    public function index($id)
    {
        $inventory = InfraInventory::find($id);
        if (!$inventory) {
            return response()->json([
                'success' => false,
                'message' => "Aucun inventaire d'équipement trouvé pour l'ID $id."
            ], 404);
        }
        $inventory->load(['items.equipment', 'items.school', 'items.user']);
        return response()->json([
            'data' => $inventory->items,
            'success' => true
        ]);
    }

    /**
     * Ajoute un ou plusieurs items (équipements) à un inventaire d'équipement.
     * POST /api/v1/infrastructures/inventories/{id}/item
     */
    public function store(Request $request, $id)
    {
        $inventory = InfraInventory::findOrFail($id);
        $user = $request->user();
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.equipment_id' => 'required|exists:infra_equipment,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $created = [];
        foreach ($data['items'] as $item) {
            $created[] = InfraInventoryEquipment::create([
                'inventory_id' => $inventory->id,
                'equipment_id' => $item['equipment_id'],
                'quantity' => $item['quantity'],
                'school_id' => $user->school_id,
                'user_id' => $user->id,
            ]);
        }
        $inventory->load(['items.equipment', 'items.school', 'items.user']);
        return response()->json([
            'data' => $inventory->items,
            'success' => true,
            'message' => count($created) . ' item(s) ajouté(s) à l\'inventaire',
        ], Response::HTTP_CREATED);
    }
}
