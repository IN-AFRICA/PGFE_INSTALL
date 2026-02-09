<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InfraStateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Query params:
     * - search: filtre texte sur le nom de l'état
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = \App\Models\InfraState::query()
            ->where('school_id', $user->school_id)
            ->orderBy('name');

        if ($search = trim((string) $request->input('search'))) {
            $query->where('name', 'like', "%{$search}%");
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
        ]);

        $state = \App\Models\InfraState::create([
            ...$validated,
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        return response()->json($state, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();

        $state = \App\Models\InfraState::where('school_id', $user->school_id)
            ->findOrFail($id);

        return response()->json($state);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $state = \App\Models\InfraState::where('school_id', $user->school_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);

        $state->update($validated);

        return response()->json($state);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $state = \App\Models\InfraState::where('school_id', $user->school_id)
            ->findOrFail($id);

        $state->delete();

        return response()->json(['message' => 'deleted']);
    }
}
