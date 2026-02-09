<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfraState extends Model
{
    protected $fillable = [
        'name', 'school_id', 'user_id',
    ];

    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
}
