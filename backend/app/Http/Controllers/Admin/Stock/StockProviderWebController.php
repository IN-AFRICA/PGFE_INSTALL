<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class StockProviderWebController extends Controller
{
    public function index(): View
    {
        $providers = StockProvider::query()->latest('id')->paginate(20);

        return view('admin.stock.providers.index', compact('providers'));
    }

    public function create(): View
    {
        return view('admin.stock.providers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['user_id'] = auth()->id();
        StockProvider::query()->create($validated);

        return redirect()->route('admin.stock-providers.index')->with('success', 'Fournisseur créé.');
    }

    public function show(StockProvider $stock_provider): View
    {
        return view('admin.stock.providers.show', ['provider' => $stock_provider]);
    }

    public function edit(StockProvider $stock_provider): View
    {
        return view('admin.stock.providers.edit', ['provider' => $stock_provider]);
    }

    public function update(Request $request, StockProvider $stock_provider): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $stock_provider->update($validated);

        return redirect()->route('admin.stock-providers.index')->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(StockProvider $stock_provider): RedirectResponse
    {
        if ($stock_provider->articles()->exists()) {
            return redirect()->route('admin.stock-providers.index')
                ->with('error', 'Impossible de supprimer : des articles référencent ce fournisseur.');
        }

        $stock_provider->delete();

        return redirect()->route('admin.stock-providers.index')->with('success', 'Fournisseur supprimé.');
    }
}
