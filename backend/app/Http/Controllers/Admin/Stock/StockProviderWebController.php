<?php

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockProviderWebController extends Controller
{
    public function index() 
    { 
        $providers = \App\Models\StockProvider::paginate(20);
        return view('admin.stock.providers.index', compact('providers')); 
    }
    public function create() { return view('admin.stock.providers.create'); }
    public function store(Request $request) { /* ... */ }
    public function show($id) { return view('admin.stock.providers.show'); }
    public function edit($id) { return view('admin.stock.providers.edit'); }
    public function update(Request $request, $id) { /* ... */ }
    public function destroy($id) { /* ... */ }
}
