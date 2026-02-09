<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class BudgetComptability extends Model
{
    protected $table = 'budget_comptability';

    protected $fillable = [
        'code',
        'start_date',
        'end_date',
    ];
}
