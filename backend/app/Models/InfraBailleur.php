<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class InfraBailleur extends Model
{
    protected $fillable = [
        'name',
        'description',
        'school_id',
        'author_id',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function author()
    {
        return $this->belongsTo(AcademicPersonal::class);
    }
}
