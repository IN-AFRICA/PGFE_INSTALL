<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Visit extends Model
{
    use AutoAssignsSchoolContext;
    use HasFactory;
    use ScopeBySchool;

    protected $fillable = [
        'academic_personal_id',
        'fonction_id',
        'classroom_id',
        'school_id',
        'visiteur',
        'subject',
        'cot_doc_prof',
        'cot_doc_eleve',
        'cot_meth_proc',
        'cot_matiere',
        'cot_march_lecon',
        'cot_enseignant',
        'cot_eleve',
        'visit_hour',
        'datefin',
        'summary',
        'created_at',
        ''
    ];

    public function academicPersonal(): BelongsTo
    {
        return $this->belongsTo(AcademicPersonal::class, 'academic_personal_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function fonction(): BelongsTo
    {
        return $this->belongsTo(Fonction::class, 'fonction_id');
    }

    protected function casts(): array
    {
        return [
            'visit_hour' => 'datetime',
            'datefin' => 'datetime',
            'cot_doc_prof' => 'float',
            'cot_doc_eleve' => 'integer',
            'cot_meth_proc' => 'float',
            'cot_matiere' => 'float',
            'cot_march_lecon' => 'float',
            'cot_enseignant' => 'float',
            'cot_eleve' => 'float',
            'created_at' => 'datetime',
        ];
    }
}
