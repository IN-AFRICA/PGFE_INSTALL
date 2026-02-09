<?php

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockExitWebController extends Controller
{
    public function index() 
    { 
        $exits = \App\Models\StockExit::with('article')->latest()->paginate(20);
        return view('admin.stock.exits.index', compact('exits')); 
    }
    public function create() { return view('admin.stock.exits.create'); }
    public function store(Request $request) { /* ... */ }
    public function show($id) { return view('admin.stock.exits.show'); }
    public function edit($id) { return view('admin.stock.exits.edit'); }
    public function update(Request $request, $id) { /* ... */ }
    public function destroy($id) { /* ... */ }
}
