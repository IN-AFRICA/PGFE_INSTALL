<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockArticle extends Model
{
    use \App\Models\Concerns\AutoAssignsSchoolContext;
    use \App\Models\Concerns\ScopeBySchool;
    use \App\Models\Concerns\HasUuid;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = [
        'name', 'category_id', 'provider_id', 'min_threshold', 'max_threshold', 'quantity', 'school_id', 'user_id',
    ];

    public function category() { return $this->belongsTo(StockCategory::class); }
    public function provider() { return $this->belongsTo(StockProvider::class); }
    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function entries() { return $this->hasMany(StockEntry::class, 'article_id'); }
    public function exits() { return $this->hasMany(StockExit::class, 'article_id'); }
    public function states() { return $this->hasMany(StockState::class, 'article_id'); }
}
