<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockInventoryArticle extends Model
{
    protected $fillable = [
        'stock_inventory_id', 'stock_article_id', 'quantity', 'note',
    ];

    public function inventory() { return $this->belongsTo(StockInventory::class, 'stock_inventory_id'); }
    public function article() { return $this->belongsTo(StockArticle::class, 'stock_article_id'); }
}
