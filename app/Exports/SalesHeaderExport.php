<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesHeaderExport implements FromCollection, WithHeadings
{
    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to   = $to;
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
                    $trx->total_price,
                    $trx->discount,
                    $trx->tax,
                    $trx->grand_total,
                    $trx->status,
                ];
            });
    }

    public function headings(): array
    {
        return ['Invoice', 'Tanggal', 'Kasir', 'Customer', 'Total', 'Diskon', 'Pajak', 'Grand Total', 'Status'];
    }
}
