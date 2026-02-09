<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Credit;
use App\Models\Debit;
use App\Models\InputAccount;

final class InputAccountObserver
{
    public function created(InputAccount $inputAccount)
    {
        // Création d'un Credit
        Credit::create([
            'name' => $inputAccount->name,
            'amount' => $inputAccount->amount,
            'justification' => $inputAccount->justification,
            'account_plan_id' => $inputAccount->account_plan_id,
            'sub_account_plan_id' => $inputAccount->sub_account_plan_id,
            'user_id' => $inputAccount->user_id,
            'school_id' => $inputAccount->school_id,
        ]);
        // Création d'un Debit
        Debit::create([
            'name' => $inputAccount->name,
            'amount' => $inputAccount->amount,
            'justification' => $inputAccount->justification,
            'account_plan_id' => $inputAccount->account_plan_id,
            'sub_account_plan_id' => $inputAccount->sub_account_plan_id,
            'user_id' => $inputAccount->user_id,
            'school_id' => $inputAccount->school_id,
        ]);
    }
}
