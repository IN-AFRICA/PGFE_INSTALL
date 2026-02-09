<?php

namespace App\Http\Controllers\Admin\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockStateWebController extends Controller
{
    public function index() 
    { 
        $states = \App\Models\StockState::with(['article', 'user'])->latest()->paginate(20);
        return view('admin.stock.states.index', compact('states')); 
    }
    public function create() { return view('admin.stock.states.create'); }
    public function store(Request $request) { /* ... */ }
    public function show($id) { return view('admin.stock.states.show'); }
    public function edit($id) { return view('admin.stock.states.edit'); }
    public function update(Request $request, $id) { /* ... */ }
    public function destroy($id) { /* ... */ }
}
