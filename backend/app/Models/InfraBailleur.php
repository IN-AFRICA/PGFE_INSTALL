<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class InfraBailleur extends Model
{
    use \App\Models\Concerns\AutoAssignsSchoolContext;
    use \App\Models\Concerns\ScopeBySchool;
    use \App\Models\Concerns\HasUuid;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'name',
        'description',
        'school_id',
        'academic_personal_id',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function author()
    {
        return $this->belongsTo(AcademicPersonal::class, 'academic_personal_id');
    }
}
