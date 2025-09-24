<?php

namespace App\Exports;

use App\Models\PurchaseReturnItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PurchaseReturnsExport implements FromCollection, WithHeadings, WithTitle
{
    protected $from;
    protected $to;

    public function __construct($from, $to) { $this->from = $from; $this->to = $to; }

    public function title(): string
    {
        return 'Retur Pembelian'; 
    }

    public function collection()
    {
        return PurchaseReturnItem::with(['purchaseReturn.supplier','product'])
            ->whereHas('purchaseReturn', fn($q) => $q->whereBetween('return_date', [$this->from, $this->to]))
            ->get()
            ->map(fn($item) => [
                'Return Number' => $item->purchaseReturn->return_number,
                'Tanggal'       => $item->purchaseReturn->return_date,
                'Supplier'      => $item->purchaseReturn->supplier?->name,
                'Product'       => $item->product?->name,
                'Qty'           => $item->quantity,
                'Price'         => $item->price,
                'Subtotal'      => $item->subtotal,
                'Note'          => $item->note,
            ]);
    }

    public function headings(): array
    {
        return ['Return Number','Tanggal','Supplier','Product','Qty','Price','Subtotal','Note'];
    }
}
