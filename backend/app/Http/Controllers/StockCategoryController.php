<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = \App\Models\StockCategory::where('school_id', $user->school_id);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        $perPage = (int) $request->input('per_page', $request->input('perPage', 15));
        $categories = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return response()->json([
            'status' => true,
            'message' => 'Liste des catégories récupérée avec succès.',
            'data' => $categories->items(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
            'links' => [
                'first' => $categories->url(1),
                'last' => $categories->url($categories->lastPage()),
                'prev' => $categories->previousPageUrl(),
                'next' => $categories->nextPageUrl(),
            ]
        ]);
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

        $category = \App\Models\StockCategory::create([
            ...$validated,
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $category = \App\Models\StockCategory::where('school_id', $user->school_id)->findOrFail($id);
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $category = \App\Models\StockCategory::where('school_id', $user->school_id)->findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);
        $category->update($validated);
        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $category = \App\Models\StockCategory::where('school_id', $user->school_id)->findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'deleted']);
    }
}
