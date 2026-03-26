<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockInventory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class StockInventoryWebController extends Controller
{
    public function index(): View
    {
        $inventories = StockInventory::query()
            ->latest('inventory_date')
            ->latest('id')
            ->paginate(20);

        return view('admin.stock.inventories.index', compact('inventories'));
    }

    public function create(): View
    {
        return view('admin.stock.inventories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'inventory_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['user_id'] = auth()->id();
        StockInventory::query()->create($validated);

        return redirect()->route('admin.stock-inventories.index')->with('success', 'Inventaire créé.');
    }

    public function show(StockInventory $stock_inventory): View
    {
        $stock_inventory->load(['articles.article', 'user']);

        return view('admin.stock.inventories.show', ['inventory' => $stock_inventory]);
    }

    public function edit(StockInventory $stock_inventory): View
    {
        return view('admin.stock.inventories.edit', ['inventory' => $stock_inventory]);
    }

    public function update(Request $request, StockInventory $stock_inventory): RedirectResponse
    {
        $validated = $request->validate([
            'inventory_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $stock_inventory->update($validated);

        return redirect()->route('admin.stock-inventories.index')->with('success', 'Inventaire mis à jour.');
    }

    public function destroy(StockInventory $stock_inventory): RedirectResponse
    {
        $stock_inventory->delete();

        return redirect()->route('admin.stock-inventories.index')->with('success', 'Inventaire supprimé.');
    }
}
