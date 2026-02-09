<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ConduiteSemester extends Model
{
    protected $fillable = [
        'conduite_id',
        'school_year_id',
        'semester_id',
    ];

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function conduite(): BelongsTo
    {
        return $this->belongsTo(Conduite::class);
    }
}
