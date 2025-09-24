<?php

namespace App\Exports;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class PurchaseSummaryExport implements FromCollection, WithHeadings, WithTitle
{
    protected $from;
    protected $to;

    public function __construct($from, $to) { $this->from = $from; $this->to = $to; }

    public function title(): string
    {
        return 'Summary PO/PR'; 
    }

    public function collection()
    {
        $orders = PurchaseOrder::with('supplier')
            ->whereBetween('order_date', [$this->from, $this->to])
            ->get()
            ->map(function($o) {
                return [
                    'Type'      => 'Purchase Order',
                    'Supplier'  => $o->supplier?->name,
                    'Nomor'     => $o->po_number,
                    'Tanggal'   => $o->order_date,
                    'Total'     => $o->total,
                    'Status'    => $o->status,
                ];
            });

        $returns = PurchaseReturn::with('supplier')
            ->whereBetween('return_date', [$this->from, $this->to])
            ->get()
            ->map(function($r) {
                return [
                    'Type'      => 'Purchase Return',
                    'Supplier'  => $r->supplier?->name,
                    'Nomor'     => $r->return_number,
                    'Tanggal'   => $r->return_date,
                    'Total'     => $r->total,
                    'Status'    => $r->status,
                ];
            });

        return (new Collection)->merge($orders)->merge($returns);
    }

    public function headings(): array
    {
        return ['Type','Supplier','Nomor','Tanggal','Total','Status'];
    }
}
