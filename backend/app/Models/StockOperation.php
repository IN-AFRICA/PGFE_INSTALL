<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOperation extends Model
{
    use \App\Models\Concerns\HasUuid;
    use HasFactory;

    protected $fillable = [
        'reference',
        'type', // 'entrée' ou 'sortie'
        'article_id',
        'quantite',
        'operateur_id',
        'school_id',
    ];

    public function article()
    {
        return $this->belongsTo(StockArticle::class, 'article_id');
    }

    public function operateur()
    {
        return $this->belongsTo(User::class, 'operateur_id');
    }
}
