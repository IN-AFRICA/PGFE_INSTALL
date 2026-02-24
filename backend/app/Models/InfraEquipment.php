<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfraEquipment extends Model
{
    protected $fillable = [
        'name', 'type_id', 'serial_number', 'location', 'state_id', 'school_id', 'user_id', 'uuid',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function type() { return $this->belongsTo(InfraType::class); }
    public function state() { return $this->belongsTo(InfraState::class); }
    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
}