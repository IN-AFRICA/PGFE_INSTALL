<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InfraInventoryRealStateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * Supporte la création d'un seul état ou de plusieurs états à la fois.
     * 
     * Payload simple (un état) :
     * {
     *   "inventory_id": 1,
     *   "state_id": 3,
     *   "note": "Plusieurs chaises cassées"
     * }
     * 
     * Payload en masse (plusieurs états) :
     * {
     *   "inventory_id": 1,
     *   "states": [
     *     { "state_id": 3, "note": "Chaises cassées" },
     *     { "state_id": 2, "note": "Tableau endommagé" }
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        // Vérifie si c'est un payload simple ou en masse
        if ($request->has('states') && is_array($request->input('states'))) {
            // Création en masse
            $validated = $request->validate([
                'inventory_id' => 'required|exists:infra_inventories,id',
                'states' => 'required|array|min:1',
                'states.*.state_id' => 'required|exists:infra_states,id',
                'states.*.note' => 'nullable|string',
            ]);

            $inventory_id = $validated['inventory_id'];
            $created_states = [];

            foreach ($validated['states'] as $stateData) {
                $item = \App\Models\InfraInventoryRealState::create([
                    'inventory_id' => $inventory_id,
                    'state_id' => $stateData['state_id'],
                    'note' => $stateData['note'] ?? null,
                    'school_id' => $user->school_id,
                    'user_id' => $user->id,
                ]);
                
                $item->load(['state.school', 'state.user', 'school', 'user']);
                $created_states[] = $item;
            }

            return response()->json([
                'data' => $created_states,
                'success' => true,
                'message' => count($created_states) . ' état(s) ajouté(s) à l\'inventaire',
            ], 201);
        }
        
        // Création simple
        $validated = $request->validate([
            'inventory_id' => 'required|exists:infra_inventories,id',
            'state_id' => 'required|exists:infra_states,id',
            'note' => 'nullable|string',
        ]);

        $item = \App\Models\InfraInventoryRealState::create([
            ...$validated,
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);
        
        // Charger les relations complètes
        $item->load(['state.school', 'state.user', 'school', 'user']);

        return response()->json([
            'data' => $item,
            'success' => true,
            'message' => 'État ajouté à l\'inventaire',
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
