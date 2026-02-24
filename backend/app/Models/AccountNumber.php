<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class AccountNumber extends Model
{
    use HasFactory;
    use SoftDeletes;
    use \App\Models\Concerns\HasUuid;

    protected $guarded = [];

    public function account(): BelongsTo
    {
        return $this->belongsTo(ClassAccount::class, 'class_account_id');
    }

    public function accountTypes()
    {
        return $this->hasMany(AccountType::class);
    }
}
