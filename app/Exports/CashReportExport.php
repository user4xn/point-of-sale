<?php

namespace App\Exports;

use App\Models\CashRegister;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CashReportExport implements FromCollection, WithHeadings
{
    protected $from;
    protected $to;

    public function __construct($from,$to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function collection()
    {
        return CashRegister::with(['user'])
            ->whereBetween('opened_at', [$this->from, $this->to])
            ->get()
            ->map(function($r) {
                return [
                    'Kasir'         => $r->user?->name,
                    'Kas Awal'      => $r->opening_amount,
                    'Kas Akhir'     => $r->closing_amount,
                    'Total Penjualan'=> $r->total_sales,
                    'Total Kas'     => $r->total_amount,
                    'Status'        => $r->status,
                    'Buka Kas'      => $r->opened_at,
                    'Tutup Kas'     => $r->closed_at,
                ];
            });
    }

    public function headings(): array
    {
        return ['Kasir','Kas Awal','Kas Akhir','Total Penjualan','Total Kas','Status','Buka Kas','Tutup Kas'];
    }
}
