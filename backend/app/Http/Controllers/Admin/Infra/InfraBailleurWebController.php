<?php

namespace App\Http\Controllers\Admin\Infra;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InfraBailleurWebController extends Controller
{
    public function index() 
    { 
        $bailleurs = \App\Models\InfraBailleur::paginate(15);
        return view('admin.infra.bailleurs.index', compact('bailleurs')); 
    }
    public function create() { return view('admin.infra.bailleurs.create'); }
    public function store(Request $request) 
    { 
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:infra_bailleurs,name',
        ]);

        \App\Models\InfraBailleur::create($validated);

        return redirect()->route('admin.infra-bailleurs.index')->with('success', 'Bailleur ajouté avec succès.');
    }

    public function show($id) 
    { 
        $bailleur = \App\Models\InfraBailleur::findOrFail($id);
        return view('admin.infra.bailleurs.show', compact('bailleur')); 
    }

    public function edit($id) 
    { 
        $bailleur = \App\Models\InfraBailleur::findOrFail($id);
        return view('admin.infra.bailleurs.edit', compact('bailleur')); 
    }

    public function update(Request $request, $id) 
    { 
        $bailleur = \App\Models\InfraBailleur::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:infra_bailleurs,name,' . $id,
        ]);

        $bailleur->update($validated);

        return redirect()->route('admin.infra-bailleurs.index')->with('success', 'Bailleur mis à jour.');
    }

    public function destroy($id) 
    { 
        $bailleur = \App\Models\InfraBailleur::findOrFail($id);
        $bailleur->delete();
        return redirect()->route('admin.infra-bailleurs.index')->with('success', 'Bailleur supprimé.');
    }
}
