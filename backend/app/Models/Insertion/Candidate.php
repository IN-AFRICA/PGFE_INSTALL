<?php

namespace App\Models\Insertion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Candidate extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'email', 'phone', 'cv', 'professional_path', 'school_id', 'user_id',
    ];
}
