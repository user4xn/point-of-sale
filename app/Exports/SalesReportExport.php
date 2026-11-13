<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesReportExport implements WithMultipleSheets
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

    public function sheets(): array
    {
        return [
            new SalesHeaderExport($this->from, $this->to, $this->cashier),
            new SalesDetailExport($this->from, $this->to, $this->cashier),
        ];
    }
}
