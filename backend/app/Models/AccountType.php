<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class AccountType extends Model
{
    use AutoAssignsSchoolContext;
    use HasFactory;
    use ScopeBySchool;
    use SoftDeletes;
    use \App\Models\Concerns\HasUuid;

    protected $guarded = [];

    public function accountNumber(): BelongsTo
    {
        return $this->belongsTo(AccountNumber::class, 'account_number_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function academicPersonal(): BelongsTo
    {
        return $this->belongsTo(AcademicPersonal::class, 'academic_personal_id');
    }

    public function inputAccounts(): HasMany|self
    {
        return $this->hasMany(InputAccount::class);
    }

    public function outputAccounts(): HasMany|self
    {
        return $this->hasMany(OutputAccount::class);
    }
}
