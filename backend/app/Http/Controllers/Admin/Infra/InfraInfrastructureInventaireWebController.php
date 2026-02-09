<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Infra;

use App\Http\Controllers\Controller;
use App\Models\InfraInfrastructure;
use App\Models\InfraInfrastructureInventaire;
use Illuminate\Http\Request;

final class InfraInfrastructureInventaireWebController extends Controller
{
    public function index()
    {
        $inventaires = InfraInfrastructureInventaire::with(['infrastructure', 'author'])->paginate(15);
        return view('admin.infra.infrastructure-inventaires.index', compact('inventaires'));
    }

    public function create()
    {
        $infrastructures = InfraInfrastructure::all();
        return view('admin.infra.infrastructure-inventaires.create', compact('infrastructures'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'infra_infrastructure_id' => 'required|exists:infra_infrastructures,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'inventory_date' => 'required|date',
            'status' => 'required|in:excellent,bon,moyen,mauvais,critique',
            'observations' => 'nullable|array',
        ]);

        $validated['author_id'] = auth()->id();
        InfraInfrastructureInventaire::create($validated);

        return redirect()->route('admin.infra-infrastructure-inventaires.index')->with('success', 'Inventaire d\'infrastructure enregistré avec succès.');
    }

    public function show($id)
    {
        $inventaire = InfraInfrastructureInventaire::with(['infrastructure', 'author', 'school'])->findOrFail($id);
        return view('admin.infra.infrastructure-inventaires.show', compact('inventaire'));
    }

    public function edit($id)
    {
        $inventaire = InfraInfrastructureInventaire::findOrFail($id);
        $infrastructures = InfraInfrastructure::all();
        return view('admin.infra.infrastructure-inventaires.edit', compact('inventaire', 'infrastructures'));
    }

    public function update(Request $request, $id)
    {
        $inventaire = InfraInfrastructureInventaire::findOrFail($id);
        $validated = $request->validate([
            'infra_infrastructure_id' => 'required|exists:infra_infrastructures,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'inventory_date' => 'required|date',
            'status' => 'required|in:excellent,bon,moyen,mauvais,critique',
            'observations' => 'nullable|array',
        ]);

        $inventaire->update($validated);

        return redirect()->route('admin.infra-infrastructure-inventaires.index')->with('success', 'Inventaire d\'infrastructure mis à jour avec succès.');
    }

    public function destroy($id)
    {
        InfraInfrastructureInventaire::findOrFail($id)->delete();
        return redirect()->route('admin.infra-infrastructure-inventaires.index')->with('success', 'Inventaire d\'infrastructure supprimé.');
    }
}
