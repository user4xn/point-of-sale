<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesReportExport implements WithMultipleSheets
{
    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function sheets(): array
    {
        return [
            new SalesHeaderExport($this->from, $this->to),
            new SalesDetailExport($this->from, $this->to),
        ];
    }
}
