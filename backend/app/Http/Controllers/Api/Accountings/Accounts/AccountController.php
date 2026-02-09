<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accountings\Accounts\StoreAccountRequest;
use App\Http\Requests\Accountings\Accounts\UpdateAccountRequest;
use App\Models\Account;
use App\Models\ClassAccount;
use App\Models\Journal;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Account::query()->latest();
        // Filtrer par école sauf pour super-admin
        if ($user && ! $user->hasRole('super-admin')) {
            $query->where('school_id', $user->school_id);
        }

        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(number) LIKE ?', ["%{$search}%"]);
            });
        }

        $accounts = $query->get();

        return response()->json([
            'data' => $accounts,
            'message' => 'Liste des comptes récupérée avec succès',
        ]);
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        try {
            $userId = Auth::user()->id;

            $account = Account::create([
                'name' => $request->name,
                'code' => $request->code,
                'number' => $request->number,
                'solde' => 0, // tu peux mettre une valeur par défaut
                'user_id' => $userId,
                'school_id' => Auth::user()->school_id, // si l'utilisateur a une école liée
            ]);
            // Écriture dans le journal
            Journal::create([
                'date' => now(),
                'description' => 'Création d’un Account',
                'montant' => null,
                'input_account_id' => null,
                'output_account_id' => null,
                'plan_comptability_id' => null,
                'sub_account_comptability_id' => null,
                'account_id' => $account->id,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la creation du compte'.$e,
            ]);
        }

        return response()->json([
            'data' => $account,
            'message' => 'Compte créé avec succès',
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $user = Auth::user();
        $account = Account::find($id);
        if (! $account) {
            return response()->json([
                'message' => 'Aucun compte trouvé pour cet identifiant.',
            ], 404);
        }
        if ($user && ! $user->hasRole('super-admin') && $account->school_id !== $user->school_id) {
            return response()->json([
                'message' => "Accès refusé : ce compte n'appartient pas à votre école.",
            ], 403);
        }
        return response()->json([
            'data' => $account,
            'message' => 'Compte récupéré avec succès',
        ]);
    }

    public function update(UpdateAccountRequest $request, Account $account): JsonResponse
    {
        $user = Auth::user();
        if ($user && ! $user->hasRole('super-admin') && $account->school_id !== $user->school_id) {
            return response()->json([
                'message' => "Accès refusé : ce compte n'appartient pas à votre école.",
            ], 403);
        }
        $account->update($request->validated());

        return response()->json([
            'data' => $account,
            'message' => 'Compte mis à jour avec succès',
        ]);
    }

    public function destroy(Account $account): JsonResponse
    {
        $user = Auth::user();
        if ($user && ! $user->hasRole('super-admin') && $account->school_id !== $user->school_id) {
            return response()->json([
                'message' => "Accès refusé : ce compte n'appartient pas à votre école.",
            ], 403);
        }
        $account->delete();

        return response()->json([
            'message' => 'Compte supprimé avec succès',
        ]);
    }
}
