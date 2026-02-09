<?php

namespace App\Http\Controllers\Admin\Infra;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InfraEtatWebController extends Controller
{
    public function index() 
    { 
        $etats = \App\Models\InfraEtat::with(['infrastructure'])->paginate(20);
        return view('admin.infra.etats.index', compact('etats')); 
    }

    public function create() 
    { 
        $infrastructures = \App\Models\InfraInfrastructure::all();
        return view('admin.infra.etats.create', compact('infrastructures')); 
    }

    public function store(Request $request) 
    { 
        $validated = $request->validate([
            'infra_infrastructure_id' => 'required|exists:infra_infrastructures,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['author_id'] = auth()->id();
        \App\Models\InfraEtat::create($validated);

        return redirect()->route('admin.infra-etats.index')->with('success', 'État enregistré.');
    }

    public function edit($id) 
    { 
        $etat = \App\Models\InfraEtat::findOrFail($id);
        $infrastructures = \App\Models\InfraInfrastructure::all();
        return view('admin.infra.etats.edit', compact('etat', 'infrastructures')); 
    }

    public function update(Request $request, $id) 
    { 
        $etat = \App\Models\InfraEtat::findOrFail($id);
        $validated = $request->validate([
            'infra_infrastructure_id' => 'required|exists:infra_infrastructures,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $etat->update($validated);
        return redirect()->route('admin.infra-etats.index')->with('success', 'État mis à jour.');
    }

    public function destroy($id) 
    { 
        \App\Models\InfraEtat::findOrFail($id)->delete();
        return redirect()->route('admin.infra-etats.index')->with('success', 'État supprimé.');
    }
}
