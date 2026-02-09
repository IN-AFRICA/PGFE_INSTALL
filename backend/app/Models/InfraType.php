<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfraType extends Model
{
    protected $fillable = [
        'name', 'school_id', 'user_id',
    ];

    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function equipment() { return $this->hasMany(InfraEquipment::class, 'type_id'); }
}
