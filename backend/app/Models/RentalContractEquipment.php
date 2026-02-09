<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchoolUser;
use App\Models\Concerns\HasUuid;
use Illuminate\Support\Str;

class RentalContractEquipment extends Model
{
    use BelongsToSchoolUser, HasUuid;

    protected $table = 'rental_contract_equipment';

    protected $fillable = [
        'contract_equipment_code',
        'rental_contract_id',
        'equipment_id',
        'quantity',
        'price',
        'is_hand_over',
    ];

    protected static function booted(): void
    {
        static::creating(function (RentalContractEquipment $item) {
            if (empty($item->contract_equipment_code)) {
                $item->contract_equipment_code = 'CTREQP-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
            }
        });
    }

    public function rentalContract() { return $this->belongsTo(RentalContract::class); }
    public function equipment() { return $this->belongsTo(Equipment::class); }
    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
}
