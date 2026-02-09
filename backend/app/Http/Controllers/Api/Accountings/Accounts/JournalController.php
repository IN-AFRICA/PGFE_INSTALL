<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;


use App\Exports\JournalsExport;
use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use function response;
use function view;
use function now;
use Illuminate\Support\Facades\DB;

final class JournalController extends Controller
{
    public function index(Request $request)
    {
        $query = Journal::query();
        if ($request->has('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->has('input_account_id')) {
            $query->where('input_account_id', $request->input_account_id);
        }
        if ($request->has('output_account_id')) {
            $query->where('output_account_id', $request->output_account_id);
        }
        if ($request->has('account_plan_id')) {
            $query->where('account_plan_id', $request->account_plan_id);
        }
        if ($request->has('sub_account_plan_id')) {
            $query->where('sub_account_plan_id', $request->sub_account_plan_id);
        }
        // Filtrage d'état: par défaut, exclure les abandonnés
        $onlyAbandoned = $request->boolean('only_abandoned') || $request->boolean('onlyAbandoned');
        $includeAbandoned = $request->boolean('include_abandoned') || $request->boolean('includeAbandoned');
        if ($onlyAbandoned) {
            $query->onlyAbandoned();
        } elseif (! $includeAbandoned) {
            $query->notAbandoned();
        }

        // Recherche par description ou libellés liés
        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('account', function ($qa) use ($search) {
                        $qa->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                    })
                    ->orWhereHas('inputAccount', function ($qi) use ($search) {
                        $qi->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                    })
                    ->orWhereHas('outputAccount', function ($qo) use ($search) {
                        $qo->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                    })
                    ->orWhereHas('accountPlan', function ($qp) use ($search) {
                        $qp->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                    })
                    ->orWhereHas('subAccountPlan', function ($qs) use ($search) {
                        $qs->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                    });
            });
        }

        $journals = $query->with(['account', 'inputAccount', 'outputAccount', 'accountPlan', 'subAccountPlan', 'linkedJournal'])->paginate(20);

        return response()->json($journals);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string',
            'montant' => 'required|numeric|gt:0',
            'input_account_id' => 'required|exists:InputAccount,id',
            'output_account_id' => 'required|exists:OutputAccount,id',
            'account_plan_id' => 'required|exists:account_plan,id',
            'sub_account_plan_id' => [
                'required',
                Rule::exists('sub_account_plan', 'id')->where(function ($query) use ($request) {
                    $query->where('account_plan_id', $request->input('account_plan_id'));
                }),
            ],
            'account_id' => 'required|exists:accounts,id',
        ], [
            // date
            'date.required' => 'La date est obligatoire.',
            'date.date' => 'La date fournie n’est pas valide.',

            // description
            'description.required' => 'La description est obligatoire.',
            'description.string' => 'La description doit être une chaîne de caractères.',

            // montant
            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.gt' => 'Le montant doit être supérieur à 0.',

            // comptes
            'input_account_id.required' => 'Le compte d’entrée est obligatoire.',
            'input_account_id.exists' => 'Le compte d’entrée sélectionné est invalide.',

            'output_account_id.required' => 'Le compte de sortie est obligatoire.',
            'output_account_id.exists' => 'Le compte de sortie sélectionné est invalide.',

            'account_plan_id.required' => 'Le plan de compte est obligatoire.',
            'account_plan_id.exists' => 'Le plan de compte sélectionné est invalide.',

            // sous-plan du compte dépendant du plan
            'sub_account_plan_id.required' => 'Le sous-plan de compte est obligatoire.',
            'sub_account_plan_id.exists' => 'Le sous-plan de compte sélectionné n’est pas valide pour le plan choisi.',

            'account_id.required' => 'Le compte est obligatoire.',
            'account_id.exists' => 'Le compte sélectionné est invalide.',
        ]);


        $journal = Journal::create([
            'date' => $validated['date'],
            'description' => $validated['description'],
            'montant' => $validated['montant'],
            'input_account_id' => $validated['input_account_id'],
            'output_account_id' => $validated['output_account_id'],
            'account_plan_id' => $validated['account_plan_id'],
            'sub_account_plan_id' => $validated['sub_account_plan_id'],
            'account_id' => $validated['account_id'],
            'abandoned' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Écriture journal créée avec input/output non nuls.',
            'journal' => $journal,
        ], 201);
    }


    // Export PDF des journaux
    public function exportPdf(Request $request)
    {
        $query = Journal::query()->with(['account', 'inputAccount', 'outputAccount', 'accountPlan', 'subAccountPlan']);

        // IDs: accepter snake_case et camelCase
        $accountId = $request->input('account_id') ?? $request->input('accountId');
        $inputAccountId = $request->input('input_account_id') ?? $request->input('inputAccountId');
        $outputAccountId = $request->input('output_account_id') ?? $request->input('outputAccountId');
        $accountPlanId = $request->input('account_plan_id') ?? $request->input('accountPlanId');
        $subAccountPlanId = $request->input('sub_account_plan_id') ?? $request->input('subAccountPlanId');

        if ($accountId !== null) {
            $query->where('account_id', (int) $accountId);
        }
        if ($inputAccountId !== null) {
            $query->where('input_account_id', (int) $inputAccountId);
        }
        if ($outputAccountId !== null) {
            $query->where('output_account_id', (int) $outputAccountId);
        }
        if ($accountPlanId !== null) {
            $query->where('account_plan_id', (int) $accountPlanId);
        }
        if ($subAccountPlanId !== null) {
            $query->where('sub_account_plan_id', (int) $subAccountPlanId);
        }

        // État: par défaut, exclure les abandonnés
        $onlyAbandoned = $request->boolean('only_abandoned') || $request->boolean('onlyAbandoned');
        $includeAbandoned = $request->boolean('include_abandoned') || $request->boolean('includeAbandoned');
        if ($onlyAbandoned) {
            $query->where('abandoned', true);
        } elseif (! $includeAbandoned) {
            $query->where('abandoned', false);
        }

        // Dates: accepter plusieurs alias, y compris 'date'
        $dateDebut = $request->input('date_debut')
            ?? $request->input('start_date')
            ?? $request->input('from')
            ?? $request->input('dateFrom')
            ?? $request->input('date');
        $dateFin = $request->input('date_fin')
            ?? $request->input('end_date')
            ?? $request->input('to')
            ?? $request->input('dateTo')
            ?? $request->input('date');

        if ($dateDebut && $dateFin) {
            $query->whereBetween('date', [$dateDebut, $dateFin]);
        } elseif ($dateDebut) {
            $query->whereDate('date', $dateDebut);
        }

        $query->orderBy('date');
        $journals = $query->get();

        $d1 = $dateDebut ?: now()->toDateString();
        $d2 = $dateFin ?: $d1;

        $html = view('exports.journals', [
            'journals' => $journals,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'journaux_'.$d1.'_to_'.$d2.'.pdf';

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    /**
     * Afficher un journal par identifiant.
     */
    public function show(Journal $journal)
    {
        $journal->load(['account', 'inputAccount', 'outputAccount', 'accountPlan', 'subAccountPlan', 'linkedJournal']);

        return response()->json([
            'success' => true,
            'message' => 'Écriture journal récupérée avec succès.',
            'data' => $journal,
        ], 200);
    }

    /**
     * Désactiver (abandonner) une écriture au lieu de supprimer.
     */
    public function destroy(Journal $journal)
    {
        if ($journal->abandoned) {
            return response()->json([
                'success' => true,
                'message' => 'Écriture déjà marquée comme abandonnée.',
                'data' => $journal,
            ], 200);
        }

        $journal->update([
            'abandoned' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Écriture journal désactivée (abandonnée) avec succès.',
            'data' => $journal->fresh(),
        ], 200);
    }

    public function update(Request $request, Journal $journal)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string',
            'montant' => 'required|numeric|gt:0',
                'input_account_id' => 'required|exists:InputAccount,id',
                'output_account_id' => 'required|exists:OutputAccount,id',
            'account_plan_id' => 'required|exists:account_plan,id',
            'sub_account_plan_id' => [
                'required',
                Rule::exists('sub_account_plan', 'id')->where(function ($query) use ($request) {
                    $query->where('account_plan_id', $request->input('account_plan_id'));
                }),
            ],
            'account_id' => 'required|exists:accounts,id',
        ]);

        $journal->update([
            'date' => $validated['date'],
            'description' => $validated['description'],
            'montant' => $validated['montant'],
            'input_account_id' => $validated['input_account_id'],
            'output_account_id' => $validated['output_account_id'],
            'account_plan_id' => $validated['account_plan_id'],
            'sub_account_plan_id' => $validated['sub_account_plan_id'],
            'account_id' => $validated['account_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Écriture journal mise à jour avec succès.',
            'data' => $journal->fresh(['account', 'inputAccount', 'outputAccount', 'accountPlan', 'subAccountPlan', 'linkedJournal']),
        ], 200);
    }
        // Nouvelle exportation : toutes les données du journal au format Excel
    public function exportExcel()
    {
        $journals = \App\Models\Journal::all();
        $data = [];
        foreach ($journals as $row) {
            $data[] = [
                $row->date,
                $row->description,
                (float) $row->montant,
                $row->account_id,
                $row->input_account_id,
                $row->output_account_id,
                $row->account_plan_id,
                $row->sub_account_plan_id,
            ];
        }
        $fileName = 'journal_export_all_' . now()->format('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\JournalsArrayExport($data),
            $fileName
        );
    }
}
