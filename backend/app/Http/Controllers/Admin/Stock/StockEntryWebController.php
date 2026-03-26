<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockArticle;
use App\Models\StockEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class StockEntryWebController extends Controller
{
    public function index(): View
    {
        $entries = StockEntry::query()
            ->with(['article.provider'])
            ->latest('entry_date')
            ->latest('id')
            ->paginate(20);

        return view('admin.stock.entries.index', compact('entries'));
    }

    public function create(): View
    {
        $articles = StockArticle::query()->orderBy('name')->get();

        return view('admin.stock.entries.create', compact('articles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'article_id' => ['required', 'exists:stock_articles,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'entry_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['user_id'] = auth()->id();

        DB::transaction(function () use ($validated): void {
            $entry = StockEntry::query()->create($validated);
            $article = StockArticle::query()->whereKey($entry->article_id)->lockForUpdate()->first();
            if ($article) {
                $article->increment('quantity', (int) $entry->quantity);
            }
        });

        return redirect()->route('admin.stock-entries.index')->with('success', 'Entrée enregistrée.');
    }

    public function show(StockEntry $stock_entry): View
    {
        $stock_entry->load('article');

        return view('admin.stock.entries.show', ['entry' => $stock_entry]);
    }

    public function edit(StockEntry $stock_entry): View
    {
        $articles = StockArticle::query()->orderBy('name')->get();

        return view('admin.stock.entries.edit', [
            'entry' => $stock_entry,
            'articles' => $articles,
        ]);
    }

    public function update(Request $request, StockEntry $stock_entry): RedirectResponse
    {
        $validated = $request->validate([
            'article_id' => ['required', 'exists:stock_articles,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'entry_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($stock_entry, $validated): void {
            $article = StockArticle::query()->whereKey($stock_entry->article_id)->lockForUpdate()->first();
            if ($article) {
                $article->decrement('quantity', (int) $stock_entry->quantity);
            }

            $stock_entry->update($validated);

            $newArticle = StockArticle::query()->whereKey($stock_entry->article_id)->lockForUpdate()->first();
            if ($newArticle) {
                $newArticle->increment('quantity', (int) $stock_entry->quantity);
            }
        });

        return redirect()->route('admin.stock-entries.index')->with('success', 'Entrée mise à jour.');
    }

    public function destroy(StockEntry $stock_entry): RedirectResponse
    {
        DB::transaction(function () use ($stock_entry): void {
            $article = StockArticle::query()->whereKey($stock_entry->article_id)->lockForUpdate()->first();
            if ($article) {
                $article->decrement('quantity', (int) $stock_entry->quantity);
            }
            $stock_entry->delete();
        });

        return redirect()->route('admin.stock-entries.index')->with('success', 'Entrée supprimée.');
    }
}
