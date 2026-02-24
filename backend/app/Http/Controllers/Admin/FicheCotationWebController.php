<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FicheCotation;
use Illuminate\Http\Request;

final class FicheCotationWebController extends Controller
{
    public function index(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);

        // Aligné sur l'API: aucune relation de semestre n'est utilisée sur fiche_cotations
        $query = FicheCotation::with(['student:id,firstname,lastname,name,matricule,school_id', 'course:id,label', 'classroom:id,name', 'schoolYear:id,name'])
            ->latest('id');

        if ($selectedSchoolId) {
            $query->whereHas('student', function ($q) use ($selectedSchoolId) {
                $q->where('school_id', $selectedSchoolId);
            });
        }

        // Recherche par élève ou cours (même logique que l'API)
        if ($request->filled('search')) {
            $search = mb_strtolower(mb_trim($request->input('search')));

            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($q2) use ($search) {
                    $q2->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(lastname) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(firstname) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(matricule) LIKE ?', ["%{$search}%"]);
                })
                ->orWhereHas('course', function ($q2) use ($search) {
                    $q2->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(label) LIKE ?', ["%{$search}%"]);
                });
            });
        }

        $ficheCotations = $query->paginate(25);

        return view('backend.pages.fiche-cotations.index', compact('ficheCotations', 'selectedSchoolId'));
    }

    private function activeSchoolId(Request $request): ?int
    {
        $user = $request->user();
        if ($user && $user->hasRole('super-admin')) {
            return session('selected_school_id');
        }

        return $user?->school_id;
    }
}
