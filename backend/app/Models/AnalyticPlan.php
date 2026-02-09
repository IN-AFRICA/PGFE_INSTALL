<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class AnalyticPlan extends Model
{
    protected $table = 'analytic_plan';

    protected $fillable = [
        'code',
        'name',
    ];
}
