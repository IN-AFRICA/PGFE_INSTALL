<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiaire;
use Illuminate\Http\Request;

final class FiliaireWebController extends Controller
{
    public function index()
    {
        $filiaires = Filiaire::query()->orderBy('name')->paginate(20, ['id', 'name']);

        return view('backend.pages.filiaires.index', compact('filiaires'));
    }

    public function create()
    {
        return view('backend.pages.filiaires.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:filiaires,name'],
        ]);
        Filiaire::create($data);

        return redirect()->route('admin.filiaires.index')->with('success', 'Filière créée.');
    }

    public function edit(Filiaire $filiaire)
    {
        return view('backend.pages.filiaires.edit', compact('filiaire'));
    }

    public function update(Request $request, Filiaire $filiaire)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:filiaires,name,'.$filiaire->id],
        ]);
        $filiaire->update($data);

        return redirect()->route('admin.filiaires.index')->with('success', 'Filière mise à jour.');
    }

    public function destroy(Filiaire $filiaire)
    {
        $filiaire->delete();

        return redirect()->route('admin.filiaires.index')->with('success', 'Filière supprimée.');
    }
}
