<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = [
        'user_id','opening_amount','closing_amount',
        'total_sales','total_amount','status','opened_at','closed_at'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function registerTransactions() {
        return $this->hasMany(CashRegisterTransaction::class);
    }

    public function transactions() {
        return $this->belongsToMany(Transaction::class, 'cash_register_transactions');
    }
}
