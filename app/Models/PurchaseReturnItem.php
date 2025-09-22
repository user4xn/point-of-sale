<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    protected $fillable = [
        'purchase_return_id',
        'product_id',
        'unit_conversion_id',
        'quantity',
        'price',
        'subtotal',
        'note',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unitConversion()
    {
        return $this->belongsTo(ProductUnitConversion::class, 'unit_conversion_id');
    }
}
