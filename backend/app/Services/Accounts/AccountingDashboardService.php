<?php

declare(strict_types=1);

namespace App\Services\Accounts;

use App\Models\Account;
use App\Models\BudgetComptability;
use App\Models\Payment;
use App\Models\SchoolYear;

final class AccountingDashboardService
{
    /**
     * Retourne les statistiques comptables (totaux, soldes, répartition, etc.) avec filtres optionnels.
     *
     * @param  array  $filters  [ex: 'account_id'=>int, 'year'=>int]
     */
    public function getAccountingStats(array $filters = []): array
    {
        // Filtre comptes
        $accountQuery = Account::query();
        if (!empty($filters['account_id'])) {
            $accountQuery->where('id', $filters['account_id']);
        }
        if (!empty($filters['account_type'])) {
            $accountQuery->where('type', $filters['account_type']);
        }
        $totalAccounts = $accountQuery->count();
        $totalBalance = $accountQuery->sum('solde');

        // Filtre budgets
        $budgetQuery = BudgetComptability::query();
        if (!empty($filters['budget_id'])) {
            $budgetQuery->where('id', $filters['budget_id']);
        }
        $totalBudgets = $budgetQuery->count();

        // Filtre année scolaire
        $schoolYearId = $filters['school_year_id'] ?? null;
        if (! $schoolYearId) {
            $activeYear = SchoolYear::active();
            $schoolYearId = $activeYear?->id;
        }
        $paymentsQuery = Payment::query();
        if ($schoolYearId) {
            $paymentsQuery->where('school_year_id', $schoolYearId);
        }
        if (!empty($filters['account_id'])) {
            $paymentsQuery->where('account_id', $filters['account_id']);
        }
        if (!empty($filters['budget_id'])) {
            $paymentsQuery->where('budget_comptability_id', $filters['budget_id']);
        }

        // Montant total payé pour l'année/budget/compte filtré
        $totalPaid = $paymentsQuery->sum('amount');
        $studentsPaid = $paymentsQuery->distinct('student_id')->count('student_id');

        // Recette (Input) : somme des montants dans InputAccount
        $inputQuery = \App\Models\InputAccount::query();
        if (!empty($filters['school_id'])) {
            $inputQuery->where('school_id', $filters['school_id']);
        }
        if (!empty($filters['account_id'])) {
            $inputQuery->where('account_plan_id', $filters['account_id']);
        }
        if (!empty($filters['school_year_id'])) {
            $inputQuery->where('school_year_id', $filters['school_year_id']);
        }
        $recette = $inputQuery->sum('amount');

        $outputQuery = \App\Models\OutputAccount::query();
        if (!empty($filters['school_id'])) {
            $outputQuery->where('school_id', $filters['school_id']);
        }
        if (!empty($filters['account_id'])) {
            $outputQuery->where('account_plan_id', $filters['account_id']);
        }
        if (!empty($filters['school_year_id'])) {
            $outputQuery->where('school_year_id', $filters['school_year_id']);
        }
        $dependances = $outputQuery->sum('amount');

        return [
            'total_accounts' => $totalAccounts,
            'total_budgets' => $totalBudgets,
            'total_balance' => $totalBalance,
            'total_paid' => $totalPaid,
            'students_paid' => $studentsPaid,
            'recette' => $recette,
            'dependances' => $dependances,
        ];
    }
}
