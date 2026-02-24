<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchoolUser;
use App\Models\Concerns\HasUuid;
use Illuminate\Support\Str;

class RentalContract extends Model
{
    use BelongsToSchoolUser, HasUuid;

    protected $fillable = [
        'contract_code',
        'client_id',
        'end_date',
        'loan_start_date',
        'total_amount',
        'loan_term',
        'interest_rate',
        'period_genre',
        'status',
        'project_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (RentalContract $contract) {
            if (empty($contract->contract_code)) {
                $contract->contract_code = 'CTR-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
            }
        });
    }

    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function equipments() { return $this->belongsToMany(Equipment::class, 'rental_contract_equipment')->withPivot(['quantity', 'price'])->withTimestamps(); }
    public function payments() { return $this->hasMany(Payment::class); }
}
