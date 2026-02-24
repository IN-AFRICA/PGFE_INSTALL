<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InfraInventoryEquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = \App\Models\InfraInventoryEquipment::with(['inventory', 'equipment'])->get();
        $data = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'inventory_id' => $item->inventory_id,
                'equipment_id' => $item->equipment_id,
                // Champs inventaire
                'inventory_date' => $item->inventory ? $item->inventory->inventory_date : null,
                'inventory_note' => $item->inventory ? $item->inventory->note : null,
                // Champs équipement
                'equipment_name' => $item->equipment ? $item->equipment->name : null,
                'equipment_serial_number' => $item->equipment ? $item->equipment->serial_number : null,
                'equipment_location' => $item->equipment ? $item->equipment->location : null,
                'equipment_type_id' => $item->equipment ? $item->equipment->type_id : null,
                'equipment_state_id' => $item->equipment ? $item->equipment->state_id : null,
                'equipment_uuid' => $item->equipment ? $item->equipment->uuid : null,
            ];
        });
        return response()->json([
            'data' => $data,
            'success' => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Supporte la création d'un seul item ou de plusieurs items à la fois.
     * 
     * Payload simple (un équipement) :
     * {
     *   "inventory_id": 1,
     *   "equipment_id": 50,
     *   "quantity": 3
     * }
     * 
     * Payload en masse (plusieurs équipements) :
     * {
     *   "inventory_id": 1,
     *   "items": [
     *     { "equipment_id": 50, "quantity": 3 },
     *     { "equipment_id": 51, "quantity": 2 }
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        // Vérifie si c'est un payload simple ou en masse
        if ($request->has('items') && is_array($request->input('items'))) {
            // Création en masse
            $validated = $request->validate([
                'inventory_id' => 'required|exists:infra_inventories,id',
                'items' => 'required|array|min:1',
                'items.*.equipment_id' => 'required|exists:infra_equipment,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            $inventory_id = $validated['inventory_id'];
            $created_items = [];

            foreach ($validated['items'] as $itemData) {
                $item = \App\Models\InfraInventoryEquipment::create([
                    'inventory_id' => $inventory_id,
                    'equipment_id' => $itemData['equipment_id'],
                    'quantity' => $itemData['quantity'],
                    'school_id' => $user->school_id,
                    'user_id' => $user->id,
                ]);
                
                $item->load(['equipment.categorie', 'equipment.bailleur', 'school', 'user']);
                $created_items[] = $item;
            }

            return response()->json([
                'data' => $created_items,
                'success' => true,
                'message' => count($created_items) . ' équipement(s) ajouté(s) à l\'inventaire',
            ], 201);
        }
        
        // Création simple
        $validated = $request->validate([
            'inventory_id' => 'required|exists:infra_inventories,id',
            'equipment_id' => 'required|exists:infra_equipment,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $item = \App\Models\InfraInventoryEquipment::create([
            ...$validated,
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);
        
        // Charger les relations complètes
        $item->load(['equipment.categorie', 'equipment.bailleur', 'school', 'user']);

        return response()->json([
            'data' => $item,
            'success' => true,
            'message' => 'Équipement ajouté à l\'inventaire',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
