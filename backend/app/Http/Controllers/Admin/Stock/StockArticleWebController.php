<?php

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockArticleWebController extends Controller
{
    public function index() 
    { 
        $articles = \App\Models\StockArticle::with(['category', 'provider'])->paginate(20);
        return view('admin.stock.articles.index', compact('articles')); 
    }

    public function create() 
    { 
        $categories = \App\Models\StockCategory::all();
        $providers = \App\Models\StockProvider::all();
        return view('admin.stock.articles.create', compact('categories', 'providers')); 
    }

    public function store(Request $request) 
    { 
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:stock_categories,id',
            'provider_id' => 'nullable|exists:stock_providers,id',
            'min_threshold' => 'nullable|integer',
            'max_threshold' => 'nullable|integer',
            'quantity' => 'required|integer|min:0',
        ]);

        $validated['user_id'] = auth()->id();
        \App\Models\StockArticle::create($validated);

        return redirect()->route('admin.stock-articles.index')->with('success', 'Article créé.');
    }
}
