<?php

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockInventoryWebController extends Controller
{
    public function index() 
    { 
        $inventories = \App\Models\StockInventory::latest()->paginate(20);
        return view('admin.stock.inventories.index', compact('inventories')); 
    }
    public function create() { return view('admin.stock.inventories.create'); }
    public function store(Request $request) { /* ... */ }
    public function show($id) { return view('admin.stock.inventories.show'); }
    public function edit($id) { return view('admin.stock.inventories.edit'); }
    public function update(Request $request, $id) { /* ... */ }
    public function destroy($id) { /* ... */ }
}
