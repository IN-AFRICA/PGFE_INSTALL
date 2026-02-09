<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Models\InputAccount;
use App\Models\Journal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class InputAccountController extends Controller
{
    // Ajouter un nouvel InputAccount
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
            'output_account_id' => 'nullable|exists:outputaccount,id',
            'account_id' => 'required_with:output_account_id|exists:accounts,id',
        ]);

        // Séparer les données pour la table inputaccount
        $user = $request->user();
        $record = $data;
        $record['user_id'] = $user->id;
        $record['school_id'] = $user->school_id;
        unset($record['output_account_id'], $record['account_id']);

        $inputAccount = null;
        DB::transaction(function () use (&$inputAccount, $record, $data) {
            $inputAccount = InputAccount::create($record);

            if (! empty($data['output_account_id'])) {
                Journal::create([
                    'date' => now()->toDateString(),
                    'description' => 'Création InputAccount: '.$data['name'],
                    'montant' => $data['amount'],
                    'input_account_id' => $inputAccount->id,
                    'output_account_id' => $data['output_account_id'],
                    'account_plan_id' => $data['account_plan_id'],
                    'sub_account_plan_id' => $data['sub_account_plan_id'],
                    'account_id' => $data['account_id'],
                ]);
            }
        });

        return response()->json([
            'data' => $inputAccount,
            'message' => 'InputAccount créé avec succès'.(! empty($data['output_account_id']) ? ' + écriture journal créée' : ''),
        ], 201);
    }

    // Liste des InputAccounts
    public function index(Request $request): JsonResponse
    {
        $query = InputAccount::query();

        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(justification) LIKE ?', ["%{$search}%"]);
            });
        }

        $inputAccounts = $query->latest()->get();

        return response()->json([
            'data' => $inputAccounts,
            'message' => 'Liste des InputAccounts récupérée avec succès',
        ]);
    }

    // Afficher un InputAccount spécifique
    public function show(InputAccount $inputAccount): JsonResponse
    {
        return response()->json([
            'data' => $inputAccount,
            'message' => 'InputAccount récupéré avec succès',
        ]);
    }

    // Mettre à jour un InputAccount
    public function update(Request $request, InputAccount $inputAccount): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'amount' => 'sometimes|numeric',
            'justification' => 'nullable|string',
            'account_plan_id' => 'sometimes|integer',
            'sub_account_plan_id' => 'nullable|integer',
            'user_id' => 'sometimes|integer',
            'school_id' => 'sometimes|integer',
        ]);
        $inputAccount->update($data);

        return response()->json([
            'data' => $inputAccount,
            'message' => 'InputAccount mis à jour avec succès',
        ]);
    }

    // Supprimer un InputAccount
    public function destroy(InputAccount $inputAccount): JsonResponse
    {
        $inputAccount->delete();

        return response()->json([
            'message' => 'InputAccount supprimé avec succès',
        ]);
    }
}
