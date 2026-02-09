<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherUnavailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_personal_id',
        'day',
        'start_time',
        'end_time',
        'reason',
    ];

    public function academicPersonal(): BelongsTo
    {
        return $this->belongsTo(AcademicPersonal::class);
    }
}
