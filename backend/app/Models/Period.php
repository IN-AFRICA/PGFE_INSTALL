<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Period extends Model
{
    protected $fillable = [
        'name',
        'school_id',
    ];
}
