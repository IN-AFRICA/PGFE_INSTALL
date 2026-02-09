<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InfraInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Query params:
     * - search: filtre texte sur la note
     * - from_date, to_date: bornes de date d'inventaire (YYYY-MM-DD)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = \App\Models\InfraInventory::query()
            ->where('school_id', $user->school_id)
            ->orderByDesc('inventory_date');

        if ($search = trim((string) $request->input('search'))) {
            $query->where('note', 'like', "%{$search}%");
        }

        if ($from = $request->input('from_date')) {
            $query->whereDate('inventory_date', '>=', $from);
        }
        if ($to = $request->input('to_date')) {
            $query->whereDate('inventory_date', '<=', $to);
        }

        return response()->json($query->get());
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

        $inventory = \App\Models\InfraInventory::create([
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

        $inventory = \App\Models\InfraInventory::where('school_id', $user->school_id)
            ->findOrFail($id);

        return response()->json($inventory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $inventory = \App\Models\InfraInventory::where('school_id', $user->school_id)
            ->findOrFail($id);

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
        $inventory = \App\Models\InfraInventory::where('school_id', $user->school_id)
            ->findOrFail($id);

        $inventory->delete();

        return response()->json(['message' => 'deleted']);
    }
}
