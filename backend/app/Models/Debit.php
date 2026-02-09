<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Debit extends Model
{
    use HasFactory;

    protected $table = 'debits';

    protected $fillable = [
        'name',
        'amount',
        'justification',
        'account_plan_id',
        'sub_account_plan_id',
        'user_id',
        'school_id',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function accountPlan(): BelongsTo
    {
        return $this->belongsTo(AccountPlan::class, 'account_plan_id');
    }

    public function subAccountPlan(): BelongsTo
    {
        return $this->belongsTo(SubAccountPlan::class, 'sub_account_plan_id');
    }
}
