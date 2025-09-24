<?php

namespace App\Exports;

use App\Models\StockOpnameItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockOpnameReportExport implements FromCollection, WithHeadings
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
        return StockOpnameItem::with(['opname.user','product'])
            ->whereHas('opname', fn($q) => $q->whereBetween('created_at', [$this->from, $this->to]))
            ->get()
            ->map(fn($item) => [
                'Tanggal Opname' => $item->opname->created_at->format('Y-m-d'),
                'Petugas'        => $item->opname->user?->name,
                'Produk'         => $item->product?->name,
                'Qty Sistem'     => $item->system_qty,
                'Qty Fisik'      => $item->physical_qty,
                'Selisih'        => $item->difference,
                'Catatan'        => $item->note,
            ]);
    }

    public function headings(): array
    {
        return ['Tanggal Opname','Petugas','Produk','Qty Sistem','Qty Fisik','Selisih','Catatan'];
    }
}
