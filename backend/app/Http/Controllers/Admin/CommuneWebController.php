<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\Province;
use Illuminate\Http\Request;

final class CommuneWebController extends Controller
{
    public function index()
    {
        $communes = Commune::query()->with('province:id,name')->orderBy('name')->paginate(20, ['id', 'name', 'province_id']);

        return view('backend.pages.communes.index', compact('communes'));
    }

    public function create()
    {
        $provinces = Province::query()->orderBy('name')->get(['id', 'name']);

        return view('backend.pages.communes.create', compact('provinces'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:communes,name'],
            'province_id' => ['nullable', 'exists:provinces,id'],
        ]);
        Commune::create($data);

        return redirect()->route('admin.communes.index')->with('success', 'Commune créée.');
    }

    public function edit(Commune $commune)
    {
        $provinces = Province::query()->orderBy('name')->get(['id', 'name']);

        return view('backend.pages.communes.edit', compact('commune', 'provinces'));
    }

    public function update(Request $request, Commune $commune)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:communes,name,'.$commune->id],
            'province_id' => ['nullable', 'exists:provinces,id'],
        ]);
        $commune->update($data);

        return redirect()->route('admin.communes.index')->with('success', 'Commune mise à jour.');
    }

    public function destroy(Commune $commune)
    {
        $commune->delete();

        return redirect()->route('admin.communes.index')->with('success', 'Commune supprimée.');
    }
}
