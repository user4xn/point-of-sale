<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesHeaderExport implements FromCollection, WithHeadings, WithTitle
{
    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function title(): string
    {
        return 'Pembelian'; 
    }

    public function collection()
    {
        return Transaction::with('user')
            ->whereBetween('created_at', [$this->from, $this->to])
            ->get()
            ->map(function ($trx) {
                return [
                    $trx->invoice_number,
                    $trx->created_at->format('Y-m-d H:i'),
                    $trx->user->name ?? '-',
                    $trx->customer_name ?? '-',
                    $trx->payment_method == 'cash' ? 'Tunai' : 'Non Tunai',
                    $trx->total_price,
                    $trx->discount,
                    $trx->tax,
                    $trx->grand_total,
                    $trx->paid_amount,
                    $trx->change_amount,
                    $trx->status,
                ];
            });
    }

    public function headings(): array
    {
        return [
          'Invoice', 'Tanggal', 'Kasir', 'Customer', 'Metode Bayar',
          'Total', 'Diskon', 'Pajak', 'Grand Total',
          'Dibayar', 'Kembali', 'Status'
        ];
    }
}
