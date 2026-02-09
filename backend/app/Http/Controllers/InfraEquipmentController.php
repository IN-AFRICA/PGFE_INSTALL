<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InfraEquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Query params:
     * - search: filtre texte sur le nom, le numéro de série ou la localisation
     * - type_id: filtrer par type
     * - state_id: filtrer par état
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = \App\Models\InfraEquipment::query()
            ->where('school_id', $user->school_id)
            ->orderBy('name');

        if ($request->filled('type_id')) {
            $query->where('type_id', (int) $request->input('type_id'));
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', (int) $request->input('state_id'));
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('per_page')) {
            $perPage = max(1, (int) $request->integer('per_page'));

            return response()->json(
                $query->paginate($perPage)
            );
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
            'name' => 'required|string|max:255',
            'type_id' => 'required|exists:infra_types,id',
            'serial_number' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'state_id' => 'nullable|exists:infra_states,id',
        ]);

        $equipment = \App\Models\InfraEquipment::create([
            ...$validated,
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        return response()->json($equipment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();

        $equipment = \App\Models\InfraEquipment::where('school_id', $user->school_id)
            ->findOrFail($id);

        return response()->json($equipment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $equipment = \App\Models\InfraEquipment::where('school_id', $user->school_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type_id' => 'sometimes|exists:infra_types,id',
            'serial_number' => 'sometimes|nullable|string|max:255',
            'location' => 'sometimes|nullable|string|max:255',
            'state_id' => 'sometimes|nullable|exists:infra_states,id',
        ]);

        $equipment->update($validated);

        return response()->json($equipment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $equipment = \App\Models\InfraEquipment::where('school_id', $user->school_id)
            ->findOrFail($id);

        $equipment->delete();

        return response()->json(['message' => 'deleted']);
    }
}
