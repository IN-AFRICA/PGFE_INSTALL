<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ValidationAureat extends Model
{
    use HasFactory;

    protected $fillable = [
        'last_name',
        'middle_name',
        'first_name',
        'registration_number',
        'gender',
        'department',
        'class',
        'year',
        'cycle',
        'present',
        'comment',
        'percentage',
    ];

    protected $casts = [
        'present' => 'boolean',
        'percentage' => 'integer',
    ];
}
