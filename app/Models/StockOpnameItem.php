<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    protected $fillable = [
        'stock_opname_id','product_id','system_qty','physical_qty','difference','note'
    ];

    public function opname() {
        return $this->belongsTo(StockOpname::class,'stock_opname_id');
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
