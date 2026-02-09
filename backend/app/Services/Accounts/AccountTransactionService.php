<?php

declare(strict_types=1);

namespace App\Services\Accounts;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;

final class AccountTransactionService
{
    public static function handleCreate(Model $model): void
    {
        self::record($model);
    }

    public static function handleUpdate(Model $model): void
    {
        self::deleteByReference($model->reference);
        self::record($model);
    }

    public static function handleDelete(Model $model): void
    {
        self::deleteByReference($model->reference);
    }

    private static function record(Model $model): void
    {
        // Pour un paiement, ne pas créer de nouveau compte, utiliser le compte existant
        if ($model instanceof Payment) {
            $account = Account::find($model->account_id);
            if ($account) {
                $account->solde += $model->amount;
                $account->save();
            }
            // Ajout de l'écriture dans InputAccount selon le modèle fourni
            $accountPlan = \App\Models\AccountPlan::first();
            $user = auth()->user();
            $subAccountPlan = $accountPlan ? \App\Models\SubAccountPlan::where('account_plan_id', $accountPlan->id)->first() : null;
            if ($accountPlan && $user) {
                \App\Models\InputAccount::create([
                    'name' => $model->reference,
                    'amount' => $model->amount,
                    'justification' => 'Entrer de paiement pour la référence '.$model->reference,
                    'account_plan_id' => $accountPlan->id,
                    'sub_account_plan_id' => $subAccountPlan?->id,
                    'user_id' => $user->id,
                    'school_id' => $user->school_id,
                ]);
            }

            return;
        }
        // Pour les autres modèles, garder la logique existante si besoin
        $data = self::getCommonData($model) + self::getSpecificData($model);
        Account::create($data);
    }

    /**
     * Données communes à Payment et Expense
     */
    private static function getCommonData(Model $model): array
    {
        $schoolId = $model->school_id ?? null;
        $userId = $model->user_id ?? null;
        $exchangeRateId = $model->exchange_rate_id ?? null;

        if ($model instanceof Payment) {
            // Dériver school_id via l'élève ou la classe
            $schoolId = $model->student->school_id
                ?? optional($model->classroom)->school_id
                ?? null;
            // Dériver exchange_rate_id depuis le frais
            $exchangeRateId = optional($model->fee)->exchange_rate_id;
            // User fallback
            $userId = $userId ?? auth()->id();
            // Ajout du compte de destination
            $accountId = $model->account_id;
        }

        // Préparer uniquement les champs existants pour Account
        $accountData = [
            'school_id' => $schoolId,
            'user_id' => $userId,
            'name' => 'Transaction automatique',
            // Aucun champ description, ni mouvement, ni transaction
        ];

        return $accountData;
    }

    /**
     * Champs spécifiques à chaque modèle
     */
    private static function getSpecificData(Model $model): array
    {
        // Ne rien retourner pour Account, ni description, ni mouvement
        return [];
    }

    private static function deleteByReference(string $reference): void
    {
        Account::where('reference', $reference)->delete();
    }

    private static function isIncoming(Model $model): bool
    {
        return $model instanceof Payment;
    }

    // Au cas où le type de compte est défini à la création du frais
    private static function resolveAccountType(Model $model): int
    {
        if ($model instanceof Payment) {
            return $model->fee->account_type_id ?? 1;
        }

        return $model->account_type_id ?? 1;
    }

    private static function convertAmount(Model $model): float
    {
        if ($model instanceof Payment) {
            $rate = optional(optional($model->fee)->exchangeRate)->rate;
            if ($rate) {
                return (float) $model->amount * (float) $rate;
            }
        } elseif (method_exists($model, 'exchangeRate') && $model->exchangeRate) {
            return (float) $model->amount * (float) $model->exchangeRate->rate;
        }

        return (float) $model->amount;
    }
}
