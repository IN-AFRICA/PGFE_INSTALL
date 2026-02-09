<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

final class SchoolWebController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');
        
        $schools = School::query()
            ->with(['province:id,name', 'country:id,name', 'type:id,title'])
            ->when($q, function($query, $q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('city', 'like', "%{$q}%");
            })
            ->latest()
            ->paginate(15)
            ->appends($request->query());

        // Stats simples pour le dashboard
        $stats = [
            'total' => School::count(),
            'provinces' => \App\Models\Province::has('schools')->count(),
        ];

        return view('backend.pages.schools.index', compact('schools', 'stats'));
    }

    public function create()
    {
        $countries = \App\Models\Country::query()->orderBy('name')->get(['id', 'name']);
        $types = \App\Models\Type::query()->orderBy('title')->get(['id', 'title']);

        return view('backend.pages.schools.create', compact('countries', 'types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:schools,name'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'type_id' => ['required', 'exists:types,id'],
            'phone_number' => ['nullable', 'string', 'max:50', 'unique:schools,phone_number'],
            'email' => ['nullable', 'email', 'max:255', 'unique:schools,email'],
            'latitude' => ['nullable', 'string', 'max:100'],
            'longitude' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'file', 'image', 'max:1024'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('schools/logos', 'public');
        }

        School::create($data);

        return redirect()->route('admin.schools.index')
            ->with('success', 'École créée avec succès.');
    }

    public function edit(School $school)
    {
        $countries = \App\Models\Country::query()->orderBy('name')->get(['id', 'name']);
        $types = \App\Models\Type::query()->orderBy('title')->get(['id', 'title']);

        return view('backend.pages.schools.edit', compact('school', 'countries', 'types'));
    }

    public function update(Request $request, School $school)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:schools,name,'.$school->id],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'type_id' => ['required', 'exists:types,id'],
            'phone_number' => ['nullable', 'string', 'max:50', 'unique:schools,phone_number,'.$school->id],
            'email' => ['nullable', 'email', 'max:255', 'unique:schools,email,'.$school->id],
            'latitude' => ['nullable', 'string', 'max:100'],
            'longitude' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'file', 'image', 'max:1024'],
        ]);
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('schools/logos', 'public');
        }
        $school->update($data);

        return redirect()->route('admin.schools.index')->with('success', 'École mise à jour.');
    }

    public function destroy(School $school)
    {
        $school->delete();

        return redirect()->route('admin.schools.index')->with('success', 'École supprimée.');
    }
}
