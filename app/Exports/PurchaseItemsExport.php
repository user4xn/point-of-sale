<?php

namespace App\Exports;


use App\Models\PurchaseOrderItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PurchaseItemsExport implements FromCollection, WithHeadings, WithTitle
{
    protected $from;
    protected $to;

    public function __construct($from, $to) { $this->from = $from; $this->to = $to; }

    public function title(): string
    {
        return 'Detail Item'; 
    }

    public function collection()
    {
        return PurchaseOrderItem::with(['purchaseOrder.supplier','product'])
            ->whereHas('purchaseOrder', fn($q) => $q->whereBetween('order_date', [$this->from, $this->to]))
            ->get()
            ->map(fn($item) => [
                'PO Number' => $item->purchaseOrder->po_number,
                'Supplier'  => $item->purchaseOrder->supplier?->name,
                'Product'   => $item->product?->name,
                'Qty'       => $item->quantity,
                'Price'     => $item->price,
                'Subtotal'  => $item->subtotal,
            ]);
    }

    public function headings(): array
    {
        return ['PO Number','Supplier','Product','Qty','Price','Subtotal'];
    }
}
