<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'sku', 'price_cents', 'stock', 'minimum_stock'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
