<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class InfraInfrastructureInventaire extends Model
{
    use AutoAssignsSchoolContext;
    use HasFactory;
    use HasUuid;
    use ScopeBySchool;
    use SoftDeletes;

    protected $fillable = [
        'infra_infrastructure_id',
        'title',
        'description',
        'inventory_date',
        'status',
        'observations',
        'school_id',
        'author_id',
    ];

    protected $casts = [
        'inventory_date' => 'date',
        'observations' => 'array',
    ];

    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(InfraInfrastructure::class, 'infra_infrastructure_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
