<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Person;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonEvaluationRequest;
use App\Models\PersonEvaluation;
use Illuminate\Http\Request;

final class PersonEvaluationController extends Controller
{
    public function index(Request $request)
    {
        $query = PersonEvaluation::with(['academicPersonal', 'schoolYear', 'school'])
            ->orderByDesc('id');

        if ($search = trim((string) $request->input('search'))) {
            $query->whereHas('academicPersonal', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('pre_name', 'like', "%$search%")
                  ->orWhere('post_name', 'like', "%$search%")
                  ->orWhere('matricule', 'like', "%$search%")
                  ->orWhereRaw("CONCAT(name, ' ', pre_name, ' ', post_name) LIKE ?", ["%$search%"]);
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $evaluations = $query->paginate($perPage)->withQueryString();

        $data = $evaluations->getCollection()->map(function (PersonEvaluation $evaluation) {
            return [
                'id' => $evaluation->id,
                'critiques' => $evaluation->critiques,
                'c1_quantite_travail' => $evaluation->c1_quantite_travail,
                'c2_theorie_pratique' => $evaluation->c2_theorie_pratique,
                'c3_determ_ress_perso' => $evaluation->c3_determ_ress_perso,
                'c4_ponctualite' => $evaluation->c4_ponctualite,
                'c5_dr_att_posit_collab' => $evaluation->c5_dr_att_posit_collab,
                'academic_personal_id' => $evaluation->academic_personal_id,
                'school_year_id' => $evaluation->school_year_id,
                'semester_id' => $evaluation->semester_id,
                'school_id' => $evaluation->school_id,
                'author_id' => $evaluation->author_id,
                'created_at' => $evaluation->created_at,
                'updated_at' => $evaluation->updated_at,
                'academic_personal' => $evaluation->academicPersonal
                    ? [
                        'id' => $evaluation->academicPersonal->id,
                        'matricule' => $evaluation->academicPersonal->matricule,
                        'name' => $evaluation->academicPersonal->name,
                        'pre_name' => $evaluation->academicPersonal->pre_name,
                        'post_name' => $evaluation->academicPersonal->post_name,
                    ]
                    : null,
                'school_year' => $evaluation->schoolYear
                    ? [
                        'id' => $evaluation->schoolYear->id,
                        'name' => $evaluation->schoolYear->name,
                    ]
                    : null,
                'school' => $evaluation->school
                    ? [
                        'id' => $evaluation->school->id,
                        'name' => $evaluation->school->name,
                    ]
                    : null,
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $evaluations->currentPage(),
                'last_page' => $evaluations->lastPage(),
                'per_page' => $evaluations->perPage(),
                'total' => $evaluations->total(),
            ],
            'success' => true,
            'message' => 'Liste des évaluations récupérée avec succès.',
        ]);
    }

    public function show($id)
    {
        $evaluation = PersonEvaluation::findOrFail($id);

        return response()->json([
            'data' => $evaluation,
            'success' => true,
            'message' => 'Evaluation recuperée avec succes.',
        ], 200);
    }

    public function store(PersonEvaluationRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $data['school_id'] = $user->school_id;
        $evaluation = PersonEvaluation::create($data);

        return response()->json([
            'data' => $evaluation,
            'success' => true,
            'message' => 'Evaluation Creee avec succes.',
        ], 201);
    }

    public function update(PersonEvaluationRequest $request, $id)
    {
        $evaluation = PersonEvaluation::findOrFail($id);
        $evaluation->update($request->validated());

        return response()->json([
            'data' => $evaluation,
            'success' => true,
            'message' => 'Evaluation Modifiee avec succes.',
        ], 200);
    }

    public function destroy($id)
    {
        $evaluation = PersonEvaluation::findOrFail($id);
        $evaluation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evaluation Supprimer avec succes.'],
            200);
    }
}
