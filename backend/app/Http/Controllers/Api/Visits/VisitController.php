<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Visits;

use App\Http\Controllers\Controller;
use App\Http\Requests\VisitRequest;
use App\Models\Visit;
use Illuminate\Http\Request;

final class VisitController extends Controller
{
    public function index(Request $request)
    {
        $query = Visit::with(['classroom', 'school']);

        // Filtres optionnels
        if ($request->filled('school_id') && is_numeric($request->input('school_id'))) {
            $query->where('school_id', (int) $request->input('school_id'));
        }
        if ($request->filled('classroom_id') && is_numeric($request->input('classroom_id'))) {
            $query->where('classroom_id', (int) $request->input('classroom_id'));
        }
        if ($request->filled('fonction_id') && is_numeric($request->input('fonction_id'))) {
            $query->where('fonction_id', (int) $request->input('fonction_id'));
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('visiteur', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%");
            });
        }
        // Date/heure de visite et datefin
        if ($request->filled('visit_hour_from')) {
            $query->where('visit_hour', '>=', $request->input('visit_hour_from'));
        }
        if ($request->filled('visit_hour_to')) {
            $query->where('visit_hour', '<=', $request->input('visit_hour_to'));
        }
        if ($request->filled('datefin_from')) {
            $query->where('datefin', '>=', $request->input('datefin_from'));
        }
        if ($request->filled('datefin_to')) {
            $query->where('datefin', '<=', $request->input('datefin_to'));
        }
        // Plages numériques pour cotations
        foreach ([
            'cot_doc_prof', 'cot_doc_eleve', 'cot_meth_proc', 'cot_matiere',
            'cot_march_lecon', 'cot_enseignant', 'cot_eleve'
        ] as $field) {
            $minKey = $field.'_min';
            $maxKey = $field.'_max';
            if ($request->filled($minKey) && is_numeric($request->input($minKey))) {
                $query->where($field, '>=', (float) $request->input($minKey));
            }
            if ($request->filled($maxKey) && is_numeric($request->input($maxKey))) {
                $query->where($field, '<=', (float) $request->input($maxKey));
            }
        }

        $perPage = (int) $request->input('per_page', 20);
        return $query->latest()->paginate($perPage);
    }

    public function store(VisitRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        // Assigner uniquement l'école depuis l'utilisateur connecté.
        // academic_personal_id est maintenant nullable et n'est plus géré ici.
        $data['school_id'] = $user?->school_id;

        $visit = Visit::create($data);
        return response()->json($visit, 201);
    }

    public function show($id)
    {
        $visit = Visit::with(['classroom', 'school'])->findOrFail($id);

        return response()->json($visit);
    }

    public function update(VisitRequest $request, $id)
    {
        $visit = Visit::findOrFail($id);
        $data = $request->validated();
        $user = $request->user();

        // Assigner uniquement l'école depuis l'utilisateur connecté.
        // academic_personal_id reste inchangé (nullable) et n'est plus recalculé ici.
        $data['school_id'] = $user?->school_id;

        $visit->update($data);
        return response()->json($visit);
    }

    public function destroy($id)
    {
        $visit = Visit::findOrFail($id);
        $visit->delete();

        return response()->json(null, 204);
    }
}
