<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        /** @var \App\Models\User|null $user */
        $user = $request->user();
        $schoolId = $user?->school_id;

        if (! $schoolId) {
            return redirect()->back()->withErrors([
                'school_id' => "Impossible de déterminer l'école de l'utilisateur connecté.",
            ])->withInput();
        }

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('filiaires', 'name')->where('school_id', $schoolId),
            ],
        ]);

        DB::transaction(function () use ($data, $schoolId): void {
            $filiaire = Filiaire::create([
                'school_id' => $schoolId,
                'name' => $data['name'],
            ]);

            $cycles = [
                'Long' => ['1er', '2nd', '3eme', '4eme', '5eme', '6eme'],
                'Court' => ['1er', '2n', '3em', '4eme'],
            ];

            foreach ($cycles as $cycleName => $levels) {
                $cycle = $filiaire->cycles()->create([
                    'school_id' => $filiaire->school_id,
                    'name' => $cycleName,
                ]);

                foreach ($levels as $levelName) {
                    $cycle->academicLevels()->create([
                        'school_id' => $filiaire->school_id,
                        'name' => $levelName,
                    ]);
                }
            }
        });

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
