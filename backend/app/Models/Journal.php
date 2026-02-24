<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Journal extends Model
{
    use ScopeBySchool;
    use SoftDeletes;
    use \App\Models\Concerns\HasUuid;
    protected $fillable = [
        'date', 'description', 'montant',
        'input_account_id', 'output_account_id',
        'account_plan_id', 'sub_account_plan_id', 'account_id',
        'linked_journal_id',
        // état
        'abandoned',
    ];

    protected $casts = [
        'abandoned' => 'boolean',
    ];

    // Scopes utilitaires (basés sur le flag booléen)
    public function scopeNotAbandoned($query)
    {
        return $query->where('abandoned', false);
    }

    public function scopeOnlyAbandoned($query)
    {
        return $query->where('abandoned', true);
    }

    public function inputAccount()
    {
        return $this->belongsTo(InputAccount::class, 'input_account_id');
    }

    public function outputAccount()
    {
        return $this->belongsTo(OutputAccount::class, 'output_account_id');
    }

    public function planComptability()
    {
        // Cette méthode est obsolète, remplacée par accountPlan()
        trigger_error('planComptability() est obsolète, utilisez accountPlan()', E_USER_DEPRECATED);

        return $this->accountPlan();
    }

    public function accountPlan()
    {
        return $this->belongsTo(AccountPlan::class, 'account_plan_id');
    }

    public function subAccountPlan()
    {
        return $this->belongsTo(SubAccountPlan::class, 'sub_account_plan_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function linkedJournal()
    {
        return $this->belongsTo(self::class, 'linked_journal_id');
    }
}
