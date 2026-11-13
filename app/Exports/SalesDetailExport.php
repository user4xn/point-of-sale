<?php

namespace App\Exports;

use App\Models\TransactionItem;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesDetailExport implements FromCollection, WithHeadings, WithTitle
{
    protected $from;
    protected $to;
    protected $cashier;

    public function __construct($from, $to, $cashier = null)
    {
        $this->from = $from;
        $this->to   = $to;
        $this->cashier = $cashier;
    }

    public function title(): string
    {
        return 'Detail Pembelian'; 
    }

    public function collection()
    {
        return TransactionItem::with(['transaction', 'product'])
            ->whereHas('transaction', function ($q) {
                $q->where('status', 'paid')
                    ->when($this->cashier, fn($q) => $q->where('user_id', $this->cashier))
                    ->whereBetween('created_at', [Carbon::parse($this->from)->startOfDay(), Carbon::parse($this->to)->endOfDay()]);
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
