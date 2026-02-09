<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = \App\Models\StockEntry::where('school_id', $user->school_id)
            ->with('article');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function($q) use ($search) {
                $q->whereHas('article', function($sq) use ($search) {
                    $sq->where('name', 'like', "%$search%");
                })->orWhere('note', 'like', "%$search%");
            });
        }

        if ($request->filled('article_id')) {
            $query->where('article_id', $request->input('article_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('entry_date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('entry_date', '<=', $request->input('to'));
        }

        $perPage = (int) $request->input('per_page', $request->input('perPage', 15));
        $entries = $query->orderByDesc('entry_date')->paginate($perPage)->withQueryString();

        return response()->json([
            'status' => true,
            'message' => 'Liste des entrées de stock récupérée avec succès.',
            'data' => $entries->items(),
            'meta' => [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
            ],
            'links' => [
                'first' => $entries->url(1),
                'last' => $entries->url($entries->lastPage()),
                'prev' => $entries->previousPageUrl(),
                'next' => $entries->nextPageUrl(),
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
            'article_id' => 'required|exists:stock_articles,id',
            'quantity' => 'required|integer|min:1',
            'entry_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        // Mise à jour du stock de l'article
        $article = \App\Models\StockArticle::findOrFail($validated['article_id']);
        $article->quantity += $validated['quantity'];
        $article->save();

        $entry = \App\Models\StockEntry::create([
            ...$validated,
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        // Enregistrement dans l'historique des opérations
        \App\Models\StockOperation::create([
            'reference' => 'ENT-' . strtoupper(uniqid()),
            'type' => 'entrée',
            'article_id' => $validated['article_id'],
            'quantite' => $validated['quantity'],
            'operateur_id' => $user->id,
            'school_id' => $user->school_id,
        ]);

        return response()->json($entry, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $entry = \App\Models\StockEntry::where('school_id', $user->school_id)->findOrFail($id);
        return response()->json($entry);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $entry = \App\Models\StockEntry::where('school_id', $user->school_id)->findOrFail($id);
        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'entry_date' => 'sometimes|date',
            'note' => 'sometimes|nullable|string',
        ]);
        $entry->update($validated);
        return response()->json($entry);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $entry = \App\Models\StockEntry::where('school_id', $user->school_id)->findOrFail($id);
        $entry->delete();
        return response()->json(['message' => 'deleted']);
    }
}
