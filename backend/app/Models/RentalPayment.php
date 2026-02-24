<?php

namespace App\Models;

use App\Traits\BelongsToSchoolUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalPayment extends Model
{
    use HasFactory, BelongsToSchoolUser, \App\Models\Concerns\HasUuid;

    protected $fillable = [
        'rental_contract_id',
        'amount',
        'payment_method',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'double',
        'paid_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(RentalContract::class, 'rental_contract_id');
    }
}
