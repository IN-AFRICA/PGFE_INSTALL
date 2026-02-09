<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\Territory;
use Illuminate\Http\Request;

final class TerritoryWebController extends Controller
{
    public function index()
    {
        $territories = Territory::query()->with('province:id,name')->orderBy('name')->paginate(20, ['id', 'name', 'province_id']);

        return view('backend.pages.territories.index', compact('territories'));
    }

    public function create()
    {
        $provinces = Province::query()->orderBy('name')->get(['id', 'name']);

        return view('backend.pages.territories.create', compact('provinces'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:territories,name'],
            'province_id' => ['nullable', 'exists:provinces,id'],
        ]);
        Territory::create($data);

        return redirect()->route('admin.territories.index')->with('success', 'Territoire créé.');
    }

    public function edit(Territory $territory)
    {
        $provinces = Province::query()->orderBy('name')->get(['id', 'name']);

        return view('backend.pages.territories.edit', compact('territory', 'provinces'));
    }

    public function update(Request $request, Territory $territory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:territories,name,'.$territory->id],
            'province_id' => ['nullable', 'exists:provinces,id'],
        ]);
        $territory->update($data);

        return redirect()->route('admin.territories.index')->with('success', 'Territoire mis à jour.');
    }

    public function destroy(Territory $territory)
    {
        $territory->delete();

        return redirect()->route('admin.territories.index')->with('success', 'Territoire supprimé.');
    }
}
