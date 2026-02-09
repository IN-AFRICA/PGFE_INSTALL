<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchoolUser;
use App\Models\Concerns\HasUuid;
use Illuminate\Support\Str;

class Equipment extends Model
{
    use BelongsToSchoolUser, HasUuid;

    protected $table = 'equipments';

    protected $fillable = [
        'equipment_code',
        'name',
        'serial_number',
        'mark_model',
        'description',
        'tech_specification',
        'comments',
        'quantity',
        'daily_price',
        'status',
        'is_available',
    ];

    protected static function booted(): void
    {
        static::creating(function (Equipment $equipment) {
            if (empty($equipment->equipment_code)) {
                $equipment->equipment_code = 'EQP-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
            }
        });
    }

    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function rentalContracts() { return $this->belongsToMany(RentalContract::class, 'rental_contract_equipment')->withPivot(['quantity', 'price'])->withTimestamps(); }
}
