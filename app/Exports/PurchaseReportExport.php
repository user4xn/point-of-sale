<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PurchaseReportExport implements WithMultipleSheets
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
            new PurchaseSummaryExport($this->from, $this->to),
            new PurchaseItemsExport($this->from, $this->to),
            new PurchaseReturnsExport($this->from, $this->to),
            new PurchaseTopSuppliersExport($this->from, $this->to),
        ];
    }
}