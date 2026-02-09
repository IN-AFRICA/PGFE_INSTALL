<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Expense extends Model
{
    use AutoAssignsSchoolContext;
    use HasFactory, SoftDeletes;
    use ScopeBySchool;
    use \App\Models\Concerns\HasUuid;

    protected $guarded = [];

    public static function generateReference(int $schoolId): string
    {
        do {
            $timestamp = now()->format('Ymd-His'); // ex: 20250723-143302
            $random = mb_strtoupper(\Illuminate\Support\Str::random(4));  // ex: ZKGW
            $ref = "EXP-{$timestamp}-{$random}-{$schoolId}";
        } while (self::where('reference', $ref)->exists());

        return $ref;
    }

    // Relations
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function exchangeRate()
    {
        return $this->belongsTo(ExchangeRate::class);
    }

    protected static function booted(): void
    {
        self::creating(function (Expense $expense) {
            if (empty($expense->reference)) {
                $expense->reference = self::generateReference($expense->school_id);
            }
        });
    }
}
