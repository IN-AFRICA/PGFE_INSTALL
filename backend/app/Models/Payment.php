<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Payment extends Model
{
    use AutoAssignsSchoolContext, HasFactory, SoftDeletes, \App\Models\Concerns\ScopeBySchool, \App\Models\Concerns\HasUuid;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'double',
        'remaining_amount' => 'double',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'account_id' => 'integer',
    ];

    public static function generateReference(int $schoolId): string
    {
        do {
            $timestamp = now()->format('Ymd-His'); // ex: 20250723-143302
            $random = mb_strtoupper(\Illuminate\Support\Str::random(4));  // ex: ZKGW
            $ref = "PAY-{$timestamp}-{$random}-{$schoolId}";
        } while (self::where('reference', $ref)->exists());

        return $ref;
    }

    // Relations essentielles
    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class, 'fee_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    // Nouvelles relations de contexte
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    protected static function booted(): void
    {
        self::creating(function (Payment $payment) {
            // Générer la référence à partir de l'école de l'élève
            if (empty($payment->reference)) {
                $schoolId = null;
                if ($payment->relationLoaded('student') && $payment->student) {
                    $schoolId = $payment->student->school_id;
                } elseif (! empty($payment->student_id)) {
                    $schoolId = Student::query()->whereKey($payment->student_id)->value('school_id');
                }
                if ($schoolId) {
                    $payment->reference = self::generateReference((int) $schoolId);
                }
            }
        });
        // Suppression du hook de création de transaction
    }
}
