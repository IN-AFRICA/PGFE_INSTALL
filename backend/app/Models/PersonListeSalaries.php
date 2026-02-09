<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PersonListeSalaries extends Model
{
    protected $fillable = [
        'academic_personal_id',
        'person_salaire_id',
        'school_id',
        'author_id',
    ];
}
