<?php

namespace App\Exports;

use App\Models\TransactionItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesDetailExport implements FromCollection, WithHeadings, WithTitle
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
        return 'Detail Pembelian'; 
    }

    public function collection()
    {
        return TransactionItem::with(['transaction', 'product'])
            ->whereHas('transaction', function ($q) {
                $q->whereBetween('created_at', [$this->from, $this->to]);
            })
            ->get()
            ->map(function ($item) {
                return [
                    $item->transaction->invoice_number,
                    $item->product->name ?? '-',
                    $item->quantity,
                    $item->unit_name,
                    $item->price,
                    $item->subtotal,
                ];
            });
    }

    public function headings(): array
    {
        return ['Invoice', 'Produk', 'Qty', 'Satuan', 'Harga', 'Subtotal'];
    }
}
