<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductReportExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::with(['category','supplier'])
            ->withCount('transactionItems')
            ->get()
            ->map(function($p) {
                $margin = $p->sell_price - $p->purchase_price;
                $marginPct = $p->purchase_price > 0
                    ? ($margin / $p->purchase_price * 100)
                    : 0;

                return [
                    'Nama Produk'   => $p->name,
                    'SKU'           => $p->sku,
                    'Kategori'      => $p->category?->name,
                    'Supplier'      => $p->supplier?->name,
                    'Stok'          => $p->stock,
                    'Harga Beli'    => $p->purchase_price,
                    'Harga Jual'    => $p->sell_price,
                    'Margin (Rp)'   => $margin,
                    'Margin (%)'    => round($marginPct,1).'%',
                    'Status'        => $p->status,
                    'Terjual'       => $p->transaction_items_count,
                    'Produk Mati'   => $p->transaction_items_count == 0 && $p->stock > 0 ? 'Ya' : 'Tidak',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nama Produk', 'SKU', 'Kategori', 'Supplier',
            'Stok', 'Harga Beli', 'Harga Jual', 'Margin (Rp)', 'Margin (%)', 'Status', 'Terjual', 'Produk Mati'
        ];
    }
}
