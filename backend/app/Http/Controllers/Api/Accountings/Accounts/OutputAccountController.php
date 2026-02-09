<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\OutputAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class OutputAccountController extends Controller
{
    // Ajouter un nouvel OutputAccount
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string',
            'amount' => 'required|numeric|gt:0',
            'justification' => 'required|string',
            'account_plan_id' => 'required|exists:account_plan,id',
            'sub_account_plan_id' => [
                'required',
                Rule::exists('sub_account_plan', 'id')->where(function ($query) use ($request) {
                    $query->where('account_plan_id', $request->input('account_plan_id'));
                }),
            ],
            // Champs optionnels pour déclencher l’écriture Journal
            'input_account_id' => 'nullable|exists:InputAccount,id',
            'account_id' => 'required_with:input_account_id|exists:accounts,id',
        ]);

        $user = $request->user();
        // Données destinées à la table outputaccount uniquement
        $record = $data;
        $record['user_id'] = $user->id;
        $record['school_id'] = $user->school_id;
        unset($record['input_account_id'], $record['account_id']);

        $outputAccount = null;

        DB::transaction(function () use (&$outputAccount, $record, $data) {
            $outputAccount = OutputAccount::create($record);

            // Si une contrepartie est fournie, créer l’écriture dans le journal
            if (! empty($data['input_account_id'])) {
                Journal::create([
                    'date' => now()->toDateString(),
                    'description' => 'Création OutputAccount: '.$data['name'],
                    'montant' => $data['amount'],
                    'input_account_id' => $data['input_account_id'],
                    'output_account_id' => $outputAccount->id,
                    'account_plan_id' => $data['account_plan_id'],
                    'sub_account_plan_id' => $data['sub_account_plan_id'],
                    'account_id' => $data['account_id'],
                ]);
            }
        });

        return response()->json([
            'data' => $outputAccount,
            'message' => 'OutputAccount créé avec succès'.(! empty($data['input_account_id']) ? ' + écriture journal créée' : ''),
        ], 201);
    }

    // Liste des OutputAccounts
    public function index(Request $request): JsonResponse
    {
        $query = OutputAccount::query();

        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(justification) LIKE ?', ["%{$search}%"]);
            });
        }

        $outputAccounts = $query->latest()->get();

        return response()->json([
            'data' => $outputAccounts,
            'message' => 'Liste des OutputAccounts récupérée avec succès',
        ]);
    }

    // Afficher un OutputAccount spécifique
    public function show(OutputAccount $outputAccount): JsonResponse
    {
        return response()->json([
            'data' => $outputAccount,
            'message' => 'OutputAccount récupéré avec succès',
        ]);
    }

    // Mettre à jour un OutputAccount
    public function update(Request $request, OutputAccount $outputAccount): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'amount' => 'sometimes|numeric',
            'justification' => 'nullable|string',
            'account_plan_id' => 'sometimes|integer',
            'sub_account_plan_id' => 'nullable|integer',

        ]);
        $outputAccount->update($data);

        return response()->json([
            'data' => $outputAccount,
            'message' => 'OutputAccount mis à jour avec succès',
        ]);
    }

    // Supprimer un OutputAccount
    public function destroy(OutputAccount $outputAccount): JsonResponse
    {
        $outputAccount->delete();

        return response()->json([
            'message' => 'OutputAccount supprimé avec succès',
        ]);
    }
}
