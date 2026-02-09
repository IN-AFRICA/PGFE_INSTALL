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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
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

        return response()->json($item, 201);
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
