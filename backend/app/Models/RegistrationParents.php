<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class RegistrationParents extends Model
{
    use AutoAssignsSchoolContext;
    use ScopeBySchool;
    use SoftDeletes;
    use \App\Models\Concerns\HasUuid;
    protected $table = 'registration_parents';

    protected $fillable = [
        'registration_id',
        'parent1_id',
        'parent2_id',
        'parent3_id',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function parent1(): BelongsTo
    {
        return $this->belongsTo(Parents::class, 'parent1_id');
    }

    public function parent2(): BelongsTo
    {
        return $this->belongsTo(Parents::class, 'parent2_id');
    }

    public function parent3(): BelongsTo
    {
        return $this->belongsTo(Parents::class, 'parent3_id');
    }
}
