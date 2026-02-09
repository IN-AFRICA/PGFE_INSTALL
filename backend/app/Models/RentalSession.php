<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchoolUser;
use App\Models\Concerns\HasUuid;
use Illuminate\Support\Str;

class RentalSession extends Model
{
    use BelongsToSchoolUser, HasUuid;

    protected $fillable = [
        'session_code',
        'equipment_id',
        'client_id',
        'status',
        'description',
    ];

    protected static function booted(): void
    {
        static::creating(function (RentalSession $session) {
            if (empty($session->session_code)) {
                $session->session_code = 'SESS-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
            }
        });
    }

    public function equipment() { return $this->belongsTo(Equipment::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
}
