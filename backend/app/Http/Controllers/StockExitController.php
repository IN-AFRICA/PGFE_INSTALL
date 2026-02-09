<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockExitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = \App\Models\StockExit::where('school_id', $user->school_id)
            ->with('article');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function($q) use ($search) {
                $q->whereHas('article', function($sq) use ($search) {
                    $sq->where('name', 'like', "%$search%");
                })->orWhere('reason', 'like', "%$search%");
            });
        }

        if ($request->filled('article_id')) {
            $query->where('article_id', $request->input('article_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('exit_date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('exit_date', '<=', $request->input('to'));
        }

        $perPage = (int) $request->input('per_page', $request->input('perPage', 15));
        $exits = $query->orderByDesc('exit_date')->paginate($perPage)->withQueryString();

        return response()->json([
            'status' => true,
            'message' => 'Liste des sorties de stock récupérée avec succès.',
            'data' => $exits->items(),
            'meta' => [
                'current_page' => $exits->currentPage(),
                'last_page' => $exits->lastPage(),
                'per_page' => $exits->perPage(),
                'total' => $exits->total(),
            ],
            'links' => [
                'first' => $exits->url(1),
                'last' => $exits->url($exits->lastPage()),
                'prev' => $exits->previousPageUrl(),
                'next' => $exits->nextPageUrl(),
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
            'exit_date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $article = \App\Models\StockArticle::findOrFail($validated['article_id']);
        if ($validated['quantity'] > $article->quantity) {
            return response()->json(['error' => 'Quantité demandée supérieure au stock disponible'], 422);
        }
        $article->quantity -= $validated['quantity'];
        $article->save();

        $exit = \App\Models\StockExit::create([
            ...$validated,
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        // Enregistrement dans l'historique des opérations
        \App\Models\StockOperation::create([
            'reference' => 'SRT-' . strtoupper(uniqid()),
            'type' => 'sortie',
            'article_id' => $validated['article_id'],
            'quantite' => $validated['quantity'],
            'operateur_id' => $user->id,
            'school_id' => $user->school_id,
        ]);

        return response()->json($exit, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $exit = \App\Models\StockExit::where('school_id', $user->school_id)->findOrFail($id);
        return response()->json($exit);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $exit = \App\Models\StockExit::where('school_id', $user->school_id)->findOrFail($id);
        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'exit_date' => 'sometimes|date',
            'reason' => 'sometimes|nullable|string',
        ]);
        $exit->update($validated);
        return response()->json($exit);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $exit = \App\Models\StockExit::where('school_id', $user->school_id)->findOrFail($id);
        $exit->delete();
        return response()->json(['message' => 'deleted']);
    }
}
