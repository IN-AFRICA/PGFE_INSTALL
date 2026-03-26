<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class StockCategoryWebController extends Controller
{
    public function index(): View
    {
        $categories = StockCategory::query()->latest('id')->paginate(20);

        return view('admin.stock.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.stock.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $validated['user_id'] = auth()->id();
        StockCategory::query()->create($validated);

        return redirect()->route('admin.stock-categories.index')->with('success', 'Catégorie créée.');
    }

    public function show(StockCategory $stock_category): View
    {
        return view('admin.stock.categories.show', ['category' => $stock_category]);
    }

    public function edit(StockCategory $stock_category): View
    {
        return view('admin.stock.categories.edit', ['category' => $stock_category]);
    }

    public function update(Request $request, StockCategory $stock_category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $stock_category->update($validated);

        return redirect()->route('admin.stock-categories.index')->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(StockCategory $stock_category): RedirectResponse
    {
        if ($stock_category->articles()->exists()) {
            return redirect()->route('admin.stock-categories.index')
                ->with('error', 'Impossible de supprimer : des articles utilisent cette catégorie.');
        }

        $stock_category->delete();

        return redirect()->route('admin.stock-categories.index')->with('success', 'Catégorie supprimée.');
    }
}
