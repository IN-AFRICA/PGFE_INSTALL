<?php

namespace App\Http\Controllers\Stock;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StockOperationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = \App\Models\StockOperation::where('school_id', $user->school_id)
            ->with(['article', 'operateur']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function($q) use ($search) {
                $q->where('type', 'like', "%$search%")
                  ->orWhere('reference', 'like', "%$search%")
                  ->orWhereHas('article', fn($sq) => $sq->where('name', 'like', "%$search%"))
                  ->orWhereHas('operateur', fn($sq) => $sq->where('name', 'like', "%$search%"));
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('article_id')) {
            $query->where('article_id', $request->input('article_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $perPage = (int) $request->input('per_page', $request->input('perPage', 15));
        $operations = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        $data = collect($operations->items())->map(function($op) {
            return [
                'id' => $op->id,
                'date' => $op->created_at,
                'reference' => $op->reference,
                'type' => $op->type,
                'article' => $op->article ? $op->article->name : null,
                'quantite' => $op->quantite,
                'operateur' => $op->operateur ? $op->operateur->name : null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Liste des opérations de stock récupérée avec succès.',
            'data' => $data,
            'meta' => [
                'current_page' => $operations->currentPage(),
                'last_page' => $operations->lastPage(),
                'per_page' => $operations->perPage(),
                'total' => $operations->total(),
            ],
            'links' => [
                'first' => $operations->url(1),
                'last' => $operations->url($operations->lastPage()),
                'prev' => $operations->previousPageUrl(),
                'next' => $operations->nextPageUrl(),
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
            'reference' => 'required|string|unique:stock_operations,reference',
            'type' => 'required|in:entrée,sortie',
            'article_id' => 'required|exists:stock_articles,id',
            'quantite' => 'required|integer|min:1',
        ]);
        $operation = \App\Models\StockOperation::create([
            ...$validated,
            'operateur_id' => $user->id,
            'academic_personal_id' => method_exists($user, 'getAcademicPersonalId') ? $user->getAcademicPersonalId() : null,
            'school_id' => $user->school_id,
        ]);
        return response()->json($operation, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $operation = \App\Models\StockOperation::where('school_id', $user->school_id)->with(['article', 'operateur'])->findOrFail($id);
        return response()->json([
            'date' => $operation->created_at,
            'reference' => $operation->reference,
            'type' => $operation->type,
            'article' => $operation->article ? $operation->article->name : null,
            'quantite' => $operation->quantite,
            'operateur' => $operation->operateur ? $operation->operateur->name : null,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $operation = \App\Models\StockOperation::where('school_id', $user->school_id)->findOrFail($id);
        $validated = $request->validate([
            'reference' => 'sometimes|string|unique:stock_operations,reference,' . $id,
            'type' => 'sometimes|in:entrée,sortie',
            'article_id' => 'sometimes|exists:stock_articles,id',
            'quantite' => 'sometimes|integer|min:1',
            'operateur_id' => 'nullable|exists:users,id',
            'academic_personal_id' => 'nullable|exists:academic_personals,id',
        ]);
        $operation->update($validated);
        return response()->json($operation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $operation = \App\Models\StockOperation::where('school_id', $user->school_id)->findOrFail($id);
        $operation->delete();
        return response()->json(['message' => 'deleted']);
    }
}
