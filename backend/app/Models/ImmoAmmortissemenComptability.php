<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ImmoAmmortissemenComptability extends Model
{
    protected $fillable = [
        'name',
        'code',
        'user_id',
        'model',
        'amount',
        'purchase_date',
        'number_years',
        'immo_account_id',
        'immo_sub_account_id',
        'school_id',
        'user_id',
    ];

    public function immoAccount(): BelongsTo
    {
        return $this->belongsTo(ImmoAccount::class);
    }

    public function immoSubAccount(): BelongsTo
    {
        return $this->belongsTo(ImmoSubAccount::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
