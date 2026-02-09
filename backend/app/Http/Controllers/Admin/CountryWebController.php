<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Throwable;

final class CountryWebController extends Controller
{
    public function index()
    {
        $countries = Country::query()->orderBy('name')->paginate(20, ['id', 'name']);

        return view('backend.pages.countries.index', compact('countries'));
    }

    public function create()
    {
        return view('backend.pages.countries.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:countries,name'],
        ]);
        Country::create($data);

        return redirect()->route('admin.countries.index')->with('success', 'Pays créé.');
    }

    public function edit(Country $country)
    {
        return view('backend.pages.countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:countries,name,'.$country->id],
        ]);
        $country->update($data);

        return redirect()->route('admin.countries.index')->with('success', 'Pays mis à jour.');
    }

    public function destroy(Country $country)
    {
        try {
            $country->delete();

            return redirect()->route('admin.countries.index')->with('success', 'Pays supprimé.');
        } catch (Throwable $e) {
            return redirect()->route('admin.countries.index')->with('error', 'Suppression impossible: '.$e->getMessage());
        }
    }
}
