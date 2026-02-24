<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlanningFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path',
        'classroom_id',
        'school_id',
        'author_id',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(AcademicPersonal::class, 'author_id');
    }
}
