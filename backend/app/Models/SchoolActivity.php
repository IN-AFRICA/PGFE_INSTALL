<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SchoolActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'description',
        'place',
        'quantity',
        'start_date',
        'end_date',
        'school_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(AcademicPersonal::class, 'author_id');
    }

    public function studentActivities(): HasMany
    {
        return $this->hasMany(StudentActivity::class, 'school_activity_id');
    }
}
