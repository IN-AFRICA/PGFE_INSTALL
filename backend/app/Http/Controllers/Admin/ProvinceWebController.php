<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Province;
use Illuminate\Http\Request;

final class ProvinceWebController extends Controller
{
    public function index()
    {
        $provinces = Province::query()->with('country:id,name')->orderBy('name')->paginate(20, ['id', 'name', 'country_id']);

        return view('backend.pages.provinces.index', compact('provinces'));
    }

    public function create()
    {
        $countries = Country::query()->orderBy('name')->get(['id', 'name']);

        return view('backend.pages.provinces.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:provinces,name'],
            'country_id' => ['nullable', 'exists:countries,id'],
        ]);
        Province::create($data);

        return redirect()->route('admin.provinces.index')->with('success', 'Province créée.');
    }

    public function edit(Province $province)
    {
        $countries = Country::query()->orderBy('name')->get(['id', 'name']);

        return view('backend.pages.provinces.edit', compact('province', 'countries'));
    }

    public function update(Request $request, Province $province)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:provinces,name,'.$province->id],
            'country_id' => ['nullable', 'exists:countries,id'],
        ]);
        $province->update($data);

        return redirect()->route('admin.provinces.index')->with('success', 'Province mise à jour.');
    }

    public function destroy(Province $province)
    {
        $province->delete();

        return redirect()->route('admin.provinces.index')->with('success', 'Province supprimée.');
    }
}
