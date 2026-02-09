<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InfraCategoryWebController extends Controller
{
    public function index() { return view('admin.infra.categories.index'); }
    public function create() { return view('admin.infra.categories.create'); }
    public function store(Request $request) { /* ... */ }
    public function show($id) { return view('admin.infra.categories.show'); }
    public function edit($id) { return view('admin.infra.categories.edit'); }
    public function update(Request $request, $id) { /* ... */ }
    public function destroy($id) { /* ... */ }
}
