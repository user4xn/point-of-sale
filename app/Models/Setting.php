<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'store_name',
        'store_address',
        'store_contact',
        'store_logo',
        'receipt_template',
        'tax_rate',
    ];
}
