<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Credit;
use App\Models\Debit;
use App\Models\OutputAccount;

final class OutputAccountObserver
{
    public function created(OutputAccount $outputAccount)
    {
        // Création d'un Debit
        Debit::create([
            'name' => $outputAccount->name,
            'amount' => $outputAccount->amount,
            'justification' => $outputAccount->justification,
            'account_plan_id' => $outputAccount->account_plan_id,
            'sub_account_plan_id' => $outputAccount->sub_account_plan_id,
            'user_id' => $outputAccount->user_id,
            'school_id' => $outputAccount->school_id,
        ]);
        // Création d'un Credit
        Credit::create([
            'name' => $outputAccount->name,
            'amount' => $outputAccount->amount,
            'justification' => $outputAccount->justification,
            'account_plan_id' => $outputAccount->account_plan_id,
            'sub_account_plan_id' => $outputAccount->sub_account_plan_id,
            'user_id' => $outputAccount->user_id,
            'school_id' => $outputAccount->school_id,
        ]);
    }
}
