<?php

namespace App\Http\Controllers\Admin\Infra;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InfraCategoryWebController extends Controller
{
    public function index() 
    { 
        $categories = \App\Models\InfraCategorie::paginate(15);
        return view('admin.infra.categories.index', compact('categories')); 
    }
    public function create() { return view('admin.infra.categories.create'); }
    public function store(Request $request) 
    { 
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:infra_categories,name',
        ]);

        \App\Models\InfraCategorie::create($validated);

        return redirect()->route('admin.infra-categories.index')->with('success', 'Catégorie créée avec succès.');
    }

    public function show($id) 
    { 
        $category = \App\Models\InfraCategorie::findOrFail($id);
        return view('admin.infra.categories.show', compact('category')); 
    }

    public function edit($id) 
    { 
        $category = \App\Models\InfraCategorie::findOrFail($id);
        return view('admin.infra.categories.edit', compact('category')); 
    }

    public function update(Request $request, $id) 
    { 
        $category = \App\Models\InfraCategorie::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:infra_categories,name,' . $id,
        ]);

        $category->update($validated);

        return redirect()->route('admin.infra-categories.index')->with('success', 'Catégorie mise à jour.');
    }

    public function destroy($id) 
    { 
        $category = \App\Models\InfraCategorie::findOrFail($id);
        $category->delete();
        return redirect()->route('admin.infra-categories.index')->with('success', 'Catégorie supprimée.');
    }
}
