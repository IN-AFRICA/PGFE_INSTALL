<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class SelectionController extends Controller
{
    /**
     * Définit la classe sélectionnée en session si elle appartient à l'école sélectionnée.
     */
    public function setClassroom(Request $request): RedirectResponse
    {
        $request->validate([
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
        ]);

        $selectedSchoolId = (int) session('selected_school_id');
        if (! $selectedSchoolId) {
            return back()->with('error', "Veuillez d'abord sélectionner une école.");
        }

        /** @var Classroom $classroom */
        $classroom = Classroom::query()->findOrFail((int) $request->integer('classroom_id'));
        if ((int) $classroom->school_id !== $selectedSchoolId) {
            return back()->with('error', 'Cette classe n’appartient pas à l’école sélectionnée.');
        }

        session(['selected_classroom_id' => (int) $classroom->id]);

        return back()->with('success', 'Classe sélectionnée.');
    }

    /**
     * Efface la classe sélectionnée de la session.
     */
    public function clearClassroom(Request $request): RedirectResponse
    {
        $request->session()->forget('selected_classroom_id');

        return back()->with('success', 'Sélection de classe réinitialisée.');
    }
}
