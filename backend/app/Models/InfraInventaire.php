<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class InfraInventaire extends Model
{
    use \App\Models\Concerns\AutoAssignsSchoolContext;
    use \App\Models\Concerns\ScopeBySchool;
    use \App\Models\Concerns\HasUuid;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = [
        'equipement_id',
        'observation',
        'school_id',
        'author_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->author_id)) {
                $model->author_id = Auth::id();
            }
            // school_id sera défini via le trait AutoAssignsSchoolContext
        });
    }

    public function equipement(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InfraEquipement::class, 'equipement_id');
    }

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
