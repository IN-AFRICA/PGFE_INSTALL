<?php

namespace App\Http\Controllers\Admin\Infra;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InfraInventaireWebController extends Controller
{
    public function index() 
    { 
        $inventaires = \App\Models\InfraInventaire::with('school')->paginate(15);
        return view('admin.infra.inventaires.index', compact('inventaires')); 
    }

    public function create() 
    { 
        $equipements = \App\Models\InfraEquipement::all();
        return view('admin.infra.inventaires.create', compact('equipements')); 
    }

    public function store(Request $request) 
    { 
        $validated = $request->validate([
            'equipement_id' => 'required|exists:infra_equipements,id',
            'observation' => 'nullable|string',
        ]);

        $validated['author_id'] = auth()->id();
        \App\Models\InfraInventaire::create($validated);

        return redirect()->route('admin.infra-inventaires.index')->with('success', 'Inventaire enregistré.');
    }

    public function edit($id) 
    { 
        $inventaire = \App\Models\InfraInventaire::findOrFail($id);
        $equipements = \App\Models\InfraEquipement::all();
        return view('admin.infra.inventaires.edit', compact('inventaire', 'equipements')); 
    }

    public function update(Request $request, $id) 
    { 
        $inventaire = \App\Models\InfraInventaire::findOrFail($id);
        $validated = $request->validate([
            'equipement_id' => 'required|exists:infra_equipements,id',
            'observation' => 'nullable|string',
        ]);

        $inventaire->update($validated);
        return redirect()->route('admin.infra-inventaires.index')->with('success', 'Inventaire mis à jour.');
    }

    public function destroy($id) 
    { 
        \App\Models\InfraInventaire::findOrFail($id)->delete();
        return redirect()->route('admin.infra-inventaires.index')->with('success', 'Inventaire supprimé.');
    }
}
