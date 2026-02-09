<?php

namespace App\Models\Insertion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobOffer extends Model
{
    use HasFactory;
    protected $fillable = [
        'title', 'description', 'company_id', 'is_open', 'school_id', 'user_id',
    ];
}
