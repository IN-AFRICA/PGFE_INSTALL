<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SubAccountPlan extends Model
{
    use \App\Models\Concerns\HasUuid;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $table = 'sub_account_plan';

    protected $fillable = [
        'account_plan_id',
        'code',
        'name',
    ];

    public function accountPlan(): BelongsTo
    {
        return $this->belongsTo(AccountPlan::class);
    }
}
