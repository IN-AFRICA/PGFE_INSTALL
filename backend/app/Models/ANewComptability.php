<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ANewComptability extends Model
{
    protected $table = 'a_new_comptability';

    protected $fillable = [
        'account_plan_id',
        'sub_account_plan_id',
        'amount',
        'type',
        'justification',
        'school_id',
        'user_id',
    ];
}
