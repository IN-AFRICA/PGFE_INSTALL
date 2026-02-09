<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PersonPresence extends Model
{
    protected $fillable = [
        'personnel_id',
        'presence',
        'absent_justified',
        'sick',
        'school_id',
        'author_id',
    ];

    protected $casts = [
        'presence' => 'boolean',
        'absent_justified' => 'boolean',
        'sick' => 'boolean',
        'personnel_id' => 'integer',
        'school_id' => 'integer',
        'author_id' => 'integer',
    ];

    public function personnel(): BelongsTo
    {
           return $this->belongsTo(AcademicPersonal::class, 'personnel_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function author(): BelongsTo
    {
           return $this->belongsTo(AcademicPersonal::class, 'author_id');
    }
}
