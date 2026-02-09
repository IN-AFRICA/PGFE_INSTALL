<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Semester extends Model
{
    protected $fillable = [
        'name',
    ];
}
