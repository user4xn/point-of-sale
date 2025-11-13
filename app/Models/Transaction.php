<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_number','payment_method','user_id','cash_register_id',
        'customer_name','total_price','discount','tax',
        'grand_total','paid_amount','change_amount','status','customer_id'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function cashRegister() {
        return $this->belongsTo(CashRegister::class);
    }

    public function items() {
        return $this->hasMany(TransactionItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
