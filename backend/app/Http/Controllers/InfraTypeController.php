<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InfraTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Query params:
     * - search: filtre texte sur le nom du type
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = \App\Models\InfraType::query()
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

        $type = \App\Models\InfraType::create([
            ...$validated,
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        return response()->json($type, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();

        $type = \App\Models\InfraType::where('school_id', $user->school_id)
            ->findOrFail($id);

        return response()->json($type);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $type = \App\Models\InfraType::where('school_id', $user->school_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);

        $type->update($validated);

        return response()->json($type);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $type = \App\Models\InfraType::where('school_id', $user->school_id)
            ->findOrFail($id);

        $type->delete();

        return response()->json(['message' => 'deleted']);
    }
}
