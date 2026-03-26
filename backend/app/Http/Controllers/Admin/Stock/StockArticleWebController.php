<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockArticle;
use App\Models\StockCategory;
use App\Models\StockProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class StockArticleWebController extends Controller
{
    public function index(): View
    {
        $articles = StockArticle::query()
            ->with(['category', 'provider'])
            ->latest('id')
            ->paginate(20);

        return view('admin.stock.articles.index', compact('articles'));
    }

    public function create(): View
    {
        $categories = StockCategory::query()->orderBy('name')->get();
        $providers = StockProvider::query()->orderBy('name')->get();

        return view('admin.stock.articles.create', compact('categories', 'providers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:stock_categories,id'],
            'provider_id' => ['nullable', 'exists:stock_providers,id'],
            'min_threshold' => ['nullable', 'integer', 'min:0'],
            'max_threshold' => ['nullable', 'integer', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $validated['user_id'] = auth()->id();
        StockArticle::query()->create($validated);

        return redirect()->route('admin.stock-articles.index')->with('success', 'Article créé.');
    }

    public function show(StockArticle $stock_article): View
    {
        $stock_article->load(['category', 'provider']);

        return view('admin.stock.articles.show', ['article' => $stock_article]);
    }

    public function edit(StockArticle $stock_article): View
    {
        $categories = StockCategory::query()->orderBy('name')->get();
        $providers = StockProvider::query()->orderBy('name')->get();

        return view('admin.stock.articles.edit', [
            'article' => $stock_article,
            'categories' => $categories,
            'providers' => $providers,
        ]);
    }

    public function update(Request $request, StockArticle $stock_article): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:stock_categories,id'],
            'provider_id' => ['nullable', 'exists:stock_providers,id'],
            'min_threshold' => ['nullable', 'integer', 'min:0'],
            'max_threshold' => ['nullable', 'integer', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $stock_article->update($validated);

        return redirect()->route('admin.stock-articles.index')->with('success', 'Article mis à jour.');
    }

    public function destroy(StockArticle $stock_article): RedirectResponse
    {
        $stock_article->delete();

        return redirect()->route('admin.stock-articles.index')->with('success', 'Article supprimé.');
    }
}
