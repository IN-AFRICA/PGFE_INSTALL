<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class DisciplinaryAction extends Model
{
    use AutoAssignsSchoolContext;
    use HasFactory;
    use ScopeBySchool;

    protected $fillable = [
        'student_id',
        'school_id',
        'type_id',
        'author_id',
        'created_at',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function author()
    {
        return $this->belongsTo(AcademicPersonal::class, 'author_id');
    }
}
