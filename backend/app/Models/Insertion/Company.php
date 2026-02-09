<?php

namespace App\Models\Insertion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'address', 'contact', 'school_id', 'user_id',
    ];
}
