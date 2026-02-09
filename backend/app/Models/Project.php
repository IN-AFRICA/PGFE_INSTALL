<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchoolUser;
use App\Models\Concerns\HasUuid;
use Illuminate\Support\Str;

class Project extends Model
{
    use BelongsToSchoolUser, HasUuid;

    protected $fillable = [
        'project_code',
        'name',
        'description',
        'start_date',
        'end_date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->project_code)) {
                $project->project_code = 'PRJ-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
            }
        });
    }

    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
}
