<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class InfraIventaireInfrastructure extends Model
{
    protected $fillable = [
        'infra_infrastructure_id',
        'description',
        'school_id',
        'author_id',
    ];
}
