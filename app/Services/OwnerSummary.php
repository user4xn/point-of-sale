<?php

namespace App\Services;

use App\Models\Expenditure;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OwnerSummary
{
    public function metrics(): array
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $now = Carbon::now();
        $start = $now->copy()->startOfMonth();
        $previousStart = $start->copy()->subMonth();
        $previousEnd = $previousStart->copy()->day(min($now->day, $previousStart->daysInMonth))
            ->setTime($now->hour, $now->minute, $now->second);
        $paid = Transaction::where('status', 'paid');
        $month = (clone $paid)->whereBetween('created_at', [$start, $now]);
        $net = fn ($query) => (float) $query->selectRaw('COALESCE(SUM(total_price - discount), 0) as net')->value('net');
        $sales = $net(clone $month);
        $previous = $net((clone $paid)->whereBetween('created_at', [$previousStart, $previousEnd]));
        $count = (clone $month)->count();
        $trendStart = $now->copy()->startOfDay()->subDays(13);
        $daily = (clone $paid)->whereBetween('created_at', [$trendStart, $now])
            ->selectRaw('DATE(created_at) as date, SUM(total_price - discount) as net_sales')
            ->groupByRaw('DATE(created_at)')->get()->keyBy('date');
        $trend = [];
        for ($day = $trendStart->copy(); $day->lte($now); $day->addDay()) {
            $date = $day->toDateString();
            $trend[] = ['date' => $date, 'net_sales' => (float) ($daily->get($date)?->net_sales ?? 0)];
        }

        return [
            'as_of' => $now->toDateTimeString(),
            'month_start' => $start->toDateString(),
            'previous_start' => $previousStart->toDateString(),
            'previous_end' => $previousEnd->toDateTimeString(),
            'net_sales' => $sales,
            'previous_net_sales' => $previous,
            'change_percent' => $previous > 0 ? round(($sales - $previous) / $previous * 100, 1) : null,
            'transactions' => $count,
            'average_order_value' => $count ? round($sales / $count, 2) : 0,
            'expenses' => (float) Expenditure::whereBetween('date', [$start->toDateString(), $now->toDateString()])->sum('amount'),
            'estimated_stock_value' => (float) Product::selectRaw('COALESCE(SUM(stock * purchase_price), 0) as value')->value('value'),
            'trend' => $trend,
        ];
    }
}
