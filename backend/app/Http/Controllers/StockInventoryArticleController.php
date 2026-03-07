<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockInventory;
use App\Models\StockInventoryArticle;
use App\Models\StockArticle;

class StockInventoryArticleController extends Controller
{
    /**
     * Ajoute un ou plusieurs articles à un inventaire.
     * POST /api/v1/stock/inventories/{inventory}/articles
     * Body: { "items": [ { "stock_article_id": 1, "quantity": 5, "note": "Observation" }, ... ] }
     */
    public function store(Request $request, $inventoryId)
    {
        $inventory = StockInventory::findOrFail($inventoryId);
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.stock_article_id' => 'required|exists:stock_articles,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.note' => 'nullable|string',
        ]);
        $created = [];
        foreach ($data['items'] as $item) {
            $created[] = StockInventoryArticle::create([
                'stock_inventory_id' => $inventory->id,
                'stock_article_id' => $item['stock_article_id'],
                'quantity' => $item['quantity'],
                'note' => $item['note'] ?? null,
            ]);
        }
        $inventory->load('articles.article');
        return response()->json([
            'success' => true,
            'data' => $inventory->articles,
        ], 201);
    }
}
