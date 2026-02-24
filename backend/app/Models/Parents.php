<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Parents extends Model
{
    use AutoAssignsSchoolContext;
    use HasFactory;
    use ScopeBySchool;
    use SoftDeletes;
    use \App\Models\Concerns\HasUuid;

    protected $fillable = [
        'id',
        'name',
        'firstname',
        'lastname',
        'genre',
        'phone_number',
        'identity_card',
        'email1',
        'phone1',
        'email2',
        'phone2',
        'school_id',
        'created_at',
        'updated_at',
    ];


    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'parents_id');
    }
}
