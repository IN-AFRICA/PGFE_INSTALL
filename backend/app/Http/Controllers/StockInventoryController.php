<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = \App\Models\StockInventory::where('school_id', $user->school_id);

        if ($search = trim((string) $request->input('search'))) {
            $query->where('note', 'like', "%$search%") ;
        }
        if ($request->filled('from')) {
            $query->where('inventory_date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('inventory_date', '<=', $request->input('to'));
        }
        $perPage = (int) $request->input('per_page', 15);
        $inventories = $query->orderByDesc('inventory_date')->paginate($perPage)->withQueryString();

        return response()->json([
            'status' => true,
            'message' => 'Liste des inventaires récupérée avec succès.',
            'data' => $inventories->items(),
            'pagination' => [
                'current_page' => $inventories->currentPage(),
                'last_page' => $inventories->lastPage(),
                'per_page' => $inventories->perPage(),
                'total' => $inventories->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'inventory_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $inventory = \App\Models\StockInventory::create([
            ...$validated,
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        return response()->json($inventory, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $inventory = \App\Models\StockInventory::where('school_id', $user->school_id)->findOrFail($id);
        return response()->json($inventory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $inventory = \App\Models\StockInventory::where('school_id', $user->school_id)->findOrFail($id);
        $validated = $request->validate([
            'inventory_date' => 'sometimes|date',
            'note' => 'sometimes|nullable|string',
        ]);
        $inventory->update($validated);
        return response()->json($inventory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $inventory = \App\Models\StockInventory::where('school_id', $user->school_id)->findOrFail($id);
        $inventory->delete();
        return response()->json(['message' => 'deleted']);
    }
}
