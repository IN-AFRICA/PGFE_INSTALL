<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Parents extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'firstname',
        'lastname',
        'genre',
        'phone_number',
        'identity_card',
        'email1',
        'phone1',
        'email2',
        'phone2',
        'created_at',
        'updated_at',
    ];


    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'parents_id');
    }
}
