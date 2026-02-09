<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicLevelRequest;
use App\Models\AcademicLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class AcademicLevelWebController extends Controller
{
    public function index(): View
    {
        $levels = AcademicLevel::query()->latest()->paginate(20);

        $breadcrumbs = [
            'title' => 'Niveaux académiques',
            'items' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Niveaux académiques', 'url' => route('admin.academic-levels.index')],
            ],
        ];

        return view('backend.pages.academic-levels.index', compact('levels', 'breadcrumbs'));
    }

    public function create(): View
    {
        $breadcrumbs = [
            'title' => 'Créer un niveau académique',
            'items' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Niveaux académiques', 'url' => route('admin.academic-levels.index')],
                ['label' => 'Créer', 'url' => '#'],
            ],
        ];

        return view('backend.pages.academic-levels.create', compact('breadcrumbs'));
    }

    public function store(AcademicLevelRequest $request): RedirectResponse
    {
        AcademicLevel::create($request->validated());

        return redirect()
            ->route('admin.academic-levels.index')
            ->with('success', 'Niveau académique créé avec succès');
    }

    public function edit(AcademicLevel $academic_level): View
    {
        $breadcrumbs = [
            'title' => 'Modifier: '.$academic_level->name,
            'items' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Niveaux académiques', 'url' => route('admin.academic-levels.index')],
                ['label' => 'Modifier', 'url' => '#'],
            ],
        ];

        return view('backend.pages.academic-levels.edit', [
            'academicLevel' => $academic_level,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function update(AcademicLevelRequest $request, AcademicLevel $academic_level): RedirectResponse
    {
        $academic_level->update($request->validated());

        return redirect()
            ->route('admin.academic-levels.index')
            ->with('success', 'Niveau académique mis à jour');
    }

    public function destroy(AcademicLevel $academic_level): RedirectResponse
    {
        $academic_level->delete();

        return redirect()
            ->route('admin.academic-levels.index')
            ->with('success', 'Niveau académique supprimé');
    }
}
