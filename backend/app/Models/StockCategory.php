<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'school_id', 'user_id',
    ];

    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function articles() { return $this->hasMany(StockArticle::class, 'category_id'); }
}
