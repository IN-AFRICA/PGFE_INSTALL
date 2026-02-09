<?php

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockCategoryWebController extends Controller
{
    public function index() 
    { 
        $categories = \App\Models\StockCategory::paginate(20);
        return view('admin.stock.categories.index', compact('categories')); 
    }
    public function create() { return view('admin.stock.categories.create'); }
    public function store(Request $request) { /* ... */ }
    public function show($id) { return view('admin.stock.categories.show'); }
    public function edit($id) { return view('admin.stock.categories.edit'); }
    public function update(Request $request, $id) { /* ... */ }
    public function destroy($id) { /* ... */ }
}
