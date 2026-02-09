<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Mechanisation extends Model
{
    use HasFactory;

    /**
     * Fix custom table name (migration creates 'mecanisations').
     */
    protected $table = 'mecanisations';

    protected $fillable = [
        'label',
        'description',
        'created_at',
    ];
}
