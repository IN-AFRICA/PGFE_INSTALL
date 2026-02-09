<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchoolUser;
use App\Models\Concerns\HasUuid;
use Illuminate\Support\Str;

class Client extends Model
{
    use BelongsToSchoolUser, HasUuid;

    protected $fillable = [
        'tenant_code',
        'name',
        'phone',
        'email',
        'address',
    ];

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (empty($client->tenant_code)) {
                $client->tenant_code = 'TEN-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
            }
        });
    }

    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function rentalContracts() { return $this->hasMany(RentalContract::class); }
}
