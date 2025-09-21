<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnitConversion extends Model
{
    protected $fillable = [
        'product_id',
        'unit_name',
        'conversion',
        'purchase_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
