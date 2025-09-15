<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegisterTransaction extends Model
{
    protected $fillable = ['cash_register_id','transaction_id'];

    public function cashRegister() {
        return $this->belongsTo(CashRegister::class);
    }

    public function transaction() {
        return $this->belongsTo(Transaction::class);
    }
}
