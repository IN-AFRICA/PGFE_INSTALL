<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Currency extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function exchangeRates()
    {
        return $this->hasMany(ExchangeRate::class);
    }

    public function activeExchangeRate()
    {
        return $this->hasOne(ExchangeRate::class)->where('is_active', true);
    }
}
