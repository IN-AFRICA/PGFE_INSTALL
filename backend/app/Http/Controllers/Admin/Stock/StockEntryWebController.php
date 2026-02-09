<?php

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockEntryWebController extends Controller
{
    public function index() 
    { 
        $entries = \App\Models\StockEntry::with('article.provider')->latest()->paginate(20);
        return view('admin.stock.entries.index', compact('entries')); 
    }
    public function create() { return view('admin.stock.entries.create'); }
    public function store(Request $request) { /* ... */ }
    public function show($id) { return view('admin.stock.entries.show'); }
    public function edit($id) { return view('admin.stock.entries.edit'); }
    public function update(Request $request, $id) { /* ... */ }
    public function destroy($id) { /* ... */ }
}
