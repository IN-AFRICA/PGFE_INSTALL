<?php

namespace App\Http\Controllers\Admin\Infra;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InfraEquipementWebController extends Controller
{
    public function index() 
    { 
        $equipements = \App\Models\InfraEquipement::with(['categorie', 'bailleur'])->paginate(20);
        return view('admin.infra.equipements.index', compact('equipements')); 
    }
    public function create() { return view('admin.infra.equipements.create'); }
    public function store(Request $request) 
    { 
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'date_acquisition' => 'nullable|date',
            'montant_acquisition' => 'nullable|numeric',
            'infra_categorie_id' => 'required|exists:infra_categories,id',
            'infra_bailleur_id' => 'nullable|exists:infra_bailleurs,id',
            'emplacement' => 'nullable|string|max:255',
        ]);

        \App\Models\InfraEquipement::create($validated);

        return redirect()->route('admin.infra-equipements.index')->with('success', 'Équipement ajouté avec succès.');
    }

    public function show($id) 
    { 
        $equipement = \App\Models\InfraEquipement::with(['categorie', 'bailleur'])->findOrFail($id);
        return view('admin.infra.equipements.show', compact('equipement')); 
    }

    public function edit($id) 
    { 
        $equipement = \App\Models\InfraEquipement::findOrFail($id);
        return view('admin.infra.equipements.edit', compact('equipement')); 
    }

    public function update(Request $request, $id) 
    { 
        $equipement = \App\Models\InfraEquipement::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'date_acquisition' => 'nullable|date',
            'montant_acquisition' => 'nullable|numeric',
            'infra_categorie_id' => 'required|exists:infra_categories,id',
            'infra_bailleur_id' => 'nullable|exists:infra_bailleurs,id',
            'emplacement' => 'nullable|string|max:255',
        ]);

        $equipement->update($validated);

        return redirect()->route('admin.infra-equipements.index')->with('success', 'Équipement mis à jour.');
    }

    public function destroy($id) 
    { 
        $equipement = \App\Models\InfraEquipement::findOrFail($id);
        $equipement->delete();
        return redirect()->route('admin.infra-equipements.index')->with('success', 'Équipement supprimé.');
    }
}
