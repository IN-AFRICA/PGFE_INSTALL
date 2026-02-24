<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockState extends Model
{
    use \App\Models\Concerns\HasUuid;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \App\Models\Concerns\ScopeBySchool;
    protected $fillable = [
        'article_id', 'quantity', 'state_date', 'note', 'school_id', 'user_id',
    ];

    public function article() { return $this->belongsTo(StockArticle::class); }
    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
}
