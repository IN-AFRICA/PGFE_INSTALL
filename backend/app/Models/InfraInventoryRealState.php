<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfraInventoryRealState extends Model
{
    use \App\Models\Concerns\HasUuid;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \App\Models\Concerns\ScopeBySchool;
    protected $fillable = [
        'inventory_id', 'state_id', 'note', 'school_id', 'user_id',
    ];

    public function inventory() { return $this->belongsTo(InfraInventory::class, 'inventory_id'); }
    public function state() { return $this->belongsTo(InfraState::class, 'state_id'); }
    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
}
