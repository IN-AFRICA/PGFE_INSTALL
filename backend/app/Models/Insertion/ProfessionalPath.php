<?php

namespace App\Models\Insertion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfessionalPath extends Model
{
    use HasFactory;
    protected $fillable = [
        'candidate_id', 'description',
    ];
}
