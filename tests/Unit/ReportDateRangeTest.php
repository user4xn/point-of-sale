<?php

use App\Exports\SalesReportExport;
use Carbon\Carbon;

it('propagates cashier filtering to both sales export sheets', function () {
    $sheets = (new SalesReportExport('2025-01-01', '2025-01-31', 42))->sheets();

    expect($sheets)->toHaveCount(2);

    foreach ($sheets as $sheet) {
        $reflection = new ReflectionClass($sheet);
        $cashier = $reflection->getProperty('cashier');
        $cashier->setAccessible(true);
        expect($cashier->getValue($sheet))->toBe(42);
    }
});

it('builds inclusive day bounds for report date filters', function () {
    expect(Carbon::parse('2025-01-31')->startOfDay()->toDateTimeString())->toBe('2025-01-31 00:00:00')
        ->and(Carbon::parse('2025-01-31')->endOfDay()->toDateTimeString())->toBe('2025-01-31 23:59:59');
});
