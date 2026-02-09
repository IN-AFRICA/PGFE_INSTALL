<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Mecanisation extends Model
{
    protected $table = 'mecanisations';

    protected $fillable = [
        'label',
        'description',
    ];
}
