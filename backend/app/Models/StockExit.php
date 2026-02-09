<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockExit extends Model
{
    protected $fillable = [
        'article_id', 'quantity', 'exit_date', 'reason', 'school_id', 'user_id',
    ];

    public function article() { return $this->belongsTo(StockArticle::class); }
    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
}
