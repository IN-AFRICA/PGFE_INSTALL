<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Model;

final class Conduite extends Model
{
    use AutoAssignsSchoolContext;
    use ScopeBySchool;

    protected $fillable = ['label', 'school_id'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
