<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockArticle;
use App\Models\StockState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class StockStateWebController extends Controller
{
    public function index(): View
    {
        $states = StockState::query()
            ->with(['article', 'user'])
            ->latest('state_date')
            ->latest('id')
            ->paginate(20);

        return view('admin.stock.states.index', compact('states'));
    }

    public function create(): View
    {
        $articles = StockArticle::query()->orderBy('name')->get();

        return view('admin.stock.states.create', compact('articles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'article_id' => ['required', 'exists:stock_articles,id'],
            'quantity' => ['required', 'integer', 'min:0'],
            'state_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['user_id'] = auth()->id();
        StockState::query()->create($validated);

        return redirect()->route('admin.stock-states.index')->with('success', 'État de stock enregistré.');
    }

    public function show(StockState $stock_state): View
    {
        $stock_state->load('article');

        return view('admin.stock.states.show', ['state' => $stock_state]);
    }

    public function edit(StockState $stock_state): View
    {
        $articles = StockArticle::query()->orderBy('name')->get();

        return view('admin.stock.states.edit', [
            'state' => $stock_state,
            'articles' => $articles,
        ]);
    }

    public function update(Request $request, StockState $stock_state): RedirectResponse
    {
        $validated = $request->validate([
            'article_id' => ['required', 'exists:stock_articles,id'],
            'quantity' => ['required', 'integer', 'min:0'],
            'state_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $stock_state->update($validated);

        return redirect()->route('admin.stock-states.index')->with('success', 'État mis à jour.');
    }

    public function destroy(StockState $stock_state): RedirectResponse
    {
        $stock_state->delete();

        return redirect()->route('admin.stock-states.index')->with('success', 'État supprimé.');
    }
}
