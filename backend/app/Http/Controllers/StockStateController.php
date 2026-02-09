<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockStateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = \App\Models\StockState::where('school_id', $user->school_id);

        if ($search = trim((string) $request->input('search'))) {
            $query->whereHas('article', function($q) use ($search) {
                $q->where('name', 'like', "%$search%") ;
            });
            $query->orWhere('note', 'like', "%$search%") ;
        }
        if ($request->filled('article_id')) {
            $query->where('article_id', $request->input('article_id'));
        }
        if ($request->filled('from')) {
            $query->where('state_date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('state_date', '<=', $request->input('to'));
        }
        $perPage = (int) $request->input('per_page', 15);
        $states = $query->orderByDesc('state_date')->paginate($perPage)->withQueryString();

        return response()->json([
            'status' => true,
            'message' => 'Liste des états de stock récupérée avec succès.',
            'data' => $states->items(),
            'pagination' => [
                'current_page' => $states->currentPage(),
                'last_page' => $states->lastPage(),
                'per_page' => $states->perPage(),
                'total' => $states->total(),
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
            'article_id' => 'required|exists:stock_articles,id',
            'quantity' => 'required|integer',
            'state_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $state = \App\Models\StockState::create([
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
        $state = \App\Models\StockState::where('school_id', $user->school_id)->findOrFail($id);
        return response()->json($state);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $state = \App\Models\StockState::where('school_id', $user->school_id)->findOrFail($id);
        $validated = $request->validate([
            'quantity' => 'sometimes|integer',
            'state_date' => 'sometimes|date',
            'note' => 'sometimes|nullable|string',
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
        $state = \App\Models\StockState::where('school_id', $user->school_id)->findOrFail($id);
        $state->delete();
        return response()->json(['message' => 'deleted']);
    }
}
