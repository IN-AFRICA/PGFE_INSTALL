<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockArticle;
use App\Models\StockExit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class StockExitWebController extends Controller
{
    public function index(): View
    {
        $exits = StockExit::query()
            ->with('article')
            ->latest('exit_date')
            ->latest('id')
            ->paginate(20);

        return view('admin.stock.exits.index', compact('exits'));
    }

    public function create(): View
    {
        $articles = StockArticle::query()->orderBy('name')->get();

        return view('admin.stock.exits.create', compact('articles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'article_id' => ['required', 'exists:stock_articles,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'exit_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['user_id'] = auth()->id();

        DB::transaction(function () use ($validated): void {
            $article = StockArticle::query()->whereKey($validated['article_id'])->lockForUpdate()->first();
            if (! $article || $article->quantity < $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => ['Stock insuffisant pour cette sortie.'],
                ]);
            }
            $article->decrement('quantity', (int) $validated['quantity']);
            StockExit::query()->create($validated);
        });

        return redirect()->route('admin.stock-exits.index')->with('success', 'Sortie enregistrée.');
    }

    public function show(StockExit $stock_exit): View
    {
        $stock_exit->load('article');

        return view('admin.stock.exits.show', ['exit' => $stock_exit]);
    }

    public function edit(StockExit $stock_exit): View
    {
        $articles = StockArticle::query()->orderBy('name')->get();

        return view('admin.stock.exits.edit', [
            'exit' => $stock_exit,
            'articles' => $articles,
        ]);
    }

    public function update(Request $request, StockExit $stock_exit): RedirectResponse
    {
        $validated = $request->validate([
            'article_id' => ['required', 'exists:stock_articles,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'exit_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($stock_exit, $validated): void {
            $oldArticle = StockArticle::query()->whereKey($stock_exit->article_id)->lockForUpdate()->first();
            if ($oldArticle) {
                $oldArticle->increment('quantity', (int) $stock_exit->quantity);
            }

            $stock_exit->update($validated);

            $newArticle = StockArticle::query()->whereKey($stock_exit->article_id)->lockForUpdate()->first();
            if (! $newArticle || $newArticle->quantity < $stock_exit->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => ['Stock insuffisant pour cette sortie.'],
                ]);
            }
            $newArticle->decrement('quantity', (int) $stock_exit->quantity);
        });

        return redirect()->route('admin.stock-exits.index')->with('success', 'Sortie mise à jour.');
    }

    public function destroy(StockExit $stock_exit): RedirectResponse
    {
        DB::transaction(function () use ($stock_exit): void {
            $article = StockArticle::query()->whereKey($stock_exit->article_id)->lockForUpdate()->first();
            if ($article) {
                $article->increment('quantity', (int) $stock_exit->quantity);
            }
            $stock_exit->delete();
        });

        return redirect()->route('admin.stock-exits.index')->with('success', 'Sortie supprimée.');
    }
}
