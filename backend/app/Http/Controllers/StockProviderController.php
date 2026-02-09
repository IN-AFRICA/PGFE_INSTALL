<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = \App\Models\StockProvider::where('school_id', $user->school_id);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('contact', 'like', "%$search%")
                  ->orWhere('address', 'like', "%$search%");
            });
        }
        $perPage = (int) $request->input('per_page', $request->input('perPage', 15));
        $providers = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return response()->json([
            'status' => true,
            'message' => 'Liste des fournisseurs récupérée avec succès.',
            'data' => $providers->items(),
            'meta' => [
                'current_page' => $providers->currentPage(),
                'last_page' => $providers->lastPage(),
                'per_page' => $providers->perPage(),
                'total' => $providers->total(),
            ],
            'links' => [
                'first' => $providers->url(1),
                'last' => $providers->url($providers->lastPage()),
                'prev' => $providers->previousPageUrl(),
                'next' => $providers->nextPageUrl(),
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
            'contact' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $provider = \App\Models\StockProvider::create([
            ...$validated,
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        return response()->json($provider, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $provider = \App\Models\StockProvider::where('school_id', $user->school_id)->findOrFail($id);
        return response()->json($provider);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $provider = \App\Models\StockProvider::where('school_id', $user->school_id)->findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact' => 'sometimes|nullable|string|max:255',
            'address' => 'sometimes|nullable|string|max:255',
        ]);
        $provider->update($validated);
        return response()->json($provider);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $provider = \App\Models\StockProvider::where('school_id', $user->school_id)->findOrFail($id);
        $provider->delete();
        return response()->json(['message' => 'deleted']);
    }
}
