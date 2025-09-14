<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'unit_id',
        'supplier_id',
        'purchase_price',
        'sell_price',
        'stock',
        'description',
        'status',
        'image',
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function unit() {
        return $this->belongsTo(Unit::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }
}
