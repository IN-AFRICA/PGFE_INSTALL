<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AccountPlan extends Model
{
    use \App\Models\Concerns\AutoAssignsSchoolContext;
    use \App\Models\Concerns\ScopeBySchool;
    use \App\Models\Concerns\HasUuid;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $table = 'account_plan';

    protected $fillable = [
        'name',
        'code',
        'class_comptability_id',
        'category_comptability_id',
        'user_id',
    ];

    public function classComptability(): BelongsTo
    {
        return $this->belongsTo(ClassComptability::class);
    }

    public function categoryComptability(): BelongsTo
    {
        return $this->belongsTo(CategoryComptability::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
