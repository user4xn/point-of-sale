<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PurchaseTopSuppliersExport implements FromCollection, WithHeadings, WithTitle
{
    protected $from;
    protected $to;

    public function __construct($from, $to) { $this->from = $from; $this->to = $to; }

    public function title(): string
    {
        return 'Top Supplier'; 
    }

    public function collection()
    {
        return PurchaseOrder::with('supplier')
            ->whereBetween('order_date', [$this->from, $this->to])
            ->selectRaw('supplier_id, COUNT(*) as total_po, SUM(total) as total_amount')
            ->groupBy('supplier_id')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn($s) => [
                'Supplier'      => $s->supplier?->name,
                'Jumlah PO'     => $s->total_po,
                'Total Belanja' => $s->total_amount,
            ]);
    }

    public function headings(): array
    {
        return ['Supplier','Jumlah PO','Total Belanja'];
    }
}
