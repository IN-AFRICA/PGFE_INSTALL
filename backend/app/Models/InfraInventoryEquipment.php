<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfraInventoryEquipment extends Model
{
    protected $fillable = [
        'inventory_id', 'equipment_id', 'quantity', 'school_id', 'user_id',
    ];

    public function inventory() { return $this->belongsTo(InfraInventory::class, 'inventory_id'); }
    public function equipment() { return $this->belongsTo(InfraEquipment::class, 'equipment_id'); }
    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
}
