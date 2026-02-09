<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OutputAccount extends Model
{
    use HasFactory;

    protected $table = 'OutputAccount';

    protected $fillable = [
        'name',
        'amount',
        'justification',
        'account_plan_id',
        'sub_account_plan_id',
        'user_id',
        'school_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (OutputAccount $model) {
            if (empty($model->school_year_id)) {
                $activeYear = \App\Models\SchoolYear::active();
                if ($activeYear) {
                    $model->school_year_id = $activeYear->id;
                }
            }
        });
    }

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

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }
}
