<?php

namespace App\Models\Insertion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Model
{
    use HasFactory;
    protected $fillable = [
        'candidate_id', 'job_offer_id', 'status', 'document_id', 'school_id', 'user_id',
    ];
}
