<?php

namespace App\Http\Controllers\Admin\Infra;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InfraInfrastructureWebController extends Controller
{
    public function index(Request $request) 
    { 
        $query = \App\Models\InfraInfrastructure::with(['categorie', 'bailleur', 'school']);

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('code', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('type')) {
            $query->where('infra_categorie_id', $request->type);
        }

        $infrastructures = $query->latest()->paginate(15);
        $types = \App\Models\InfraCategorie::all();

        return view('admin.infra.infrastructures.index', compact('infrastructures', 'types')); 
    }
    public function create() { return view('admin.infra.infrastructures.create'); }
    public function store(Request $request) 
    { 
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'date_construction' => 'nullable|date',
            'montant_construction' => 'nullable|numeric',
            'infra_categorie_id' => 'required|exists:infra_categories,id',
            'infra_bailleur_id' => 'nullable|exists:infra_bailleurs,id',
            'description' => 'nullable|string',
            'emplacement' => 'nullable|string|max:255',
        ]);

        \App\Models\InfraInfrastructure::create($validated);

        return redirect()->route('admin.infra-infrastructures.index')->with('success', 'Infrastructure créée avec succès.');
    }

    public function show($id) 
    { 
        $infrastructure = \App\Models\InfraInfrastructure::with(['categorie', 'bailleur', 'school'])->findOrFail($id);
        return view('admin.infra.infrastructures.show', compact('infrastructure')); 
    }

    public function edit($id) 
    { 
        $infrastructure = \App\Models\InfraInfrastructure::findOrFail($id);
        return view('admin.infra.infrastructures.edit', compact('infrastructure')); 
    }

    public function update(Request $request, $id) 
    { 
        $infrastructure = \App\Models\InfraInfrastructure::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'date_construction' => 'nullable|date',
            'montant_construction' => 'nullable|numeric',
            'infra_categorie_id' => 'required|exists:infra_categories,id',
            'infra_bailleur_id' => 'nullable|exists:infra_bailleurs,id',
            'description' => 'nullable|string',
            'emplacement' => 'nullable|string|max:255',
        ]);

        $infrastructure->update($validated);

        return redirect()->route('admin.infra-infrastructures.index')->with('success', 'Infrastructure mise à jour avec succès.');
    }

    public function destroy($id) 
    { 
        $infrastructure = \App\Models\InfraInfrastructure::findOrFail($id);
        $infrastructure->delete();
        return redirect()->route('admin.infra-infrastructures.index')->with('success', 'Infrastructure supprimée.');
    }
}
