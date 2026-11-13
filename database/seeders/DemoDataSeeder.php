<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Data demo hanya boleh dibuat di environment local atau testing.');
        }

        DB::transaction(function () {
            if (DB::table('products')->where('sku', 'DEMO-001')->exists()) {
                $this->command?->warn('Dataset DEMO sudah ada; tidak ditambahkan ulang.');

                return;
            }

            $stamp = now()->subDays(90)->startOfDay();
            $timestamps = ['created_at' => $stamp, 'updated_at' => $stamp];
            $unit = DB::table('units')->insertGetId(['name' => 'DEMO pcs', ...$timestamps]);
            $categories = [];
            foreach (['Minuman', 'Makanan ringan', 'Sembako', 'Kebersihan', 'Perawatan'] as $name) {
                $categories[] = DB::table('categories')->insertGetId(['name' => 'DEMO '.$name, ...$timestamps]);
            }
            $suppliers = [];
            for ($i = 1; $i <= 10; $i++) {
                $suppliers[] = DB::table('suppliers')->insertGetId([
                    'name' => 'DEMO Supplier '.$i, 'contact' => 'Kontak demo '.$i,
                    'email' => "supplier{$i}@example.com", 'address' => 'Alamat simulasi '.$i, ...$timestamps,
                ]);
            }
            $users = [];
            for ($i = 1; $i <= 3; $i++) {
                $users[] = DB::table('users')->insertGetId([
                    'name' => 'DEMO Kasir '.$i, 'email' => "demo.kasir{$i}@example.com",
                    'password' => Hash::make(Str::random(40)), 'role' => 'cashier',
                    'email_verified_at' => $stamp, ...$timestamps,
                ]);
            }
            $customers = [];
            for ($i = 1; $i <= 60; $i++) {
                $customers[] = DB::table('customers')->insertGetId([
                    'name' => 'DEMO Pelanggan '.$i, 'email' => "pelanggan{$i}@example.com",
                    'phone' => 'DEMO-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                    'address' => 'Alamat pelanggan simulasi '.$i, 'points' => 0, ...$timestamps,
                ]);
            }
            $products = [];
            for ($i = 0; $i < 100; $i++) {
                $cost = 2000 + ($i % 25) * 1000;
                $id = DB::table('products')->insertGetId([
                    'name' => 'DEMO Produk '.($i + 1), 'sku' => 'DEMO-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                    'category_id' => $categories[$i % 5], 'unit_id' => $unit,
                    'supplier_id' => $suppliers[$i % 10], 'purchase_price' => $cost,
                    'sell_price' => $cost + 2000, 'stock' => $i >= 95 ? $i - 95 : 300,
                    'description' => 'Data simulasi; stok awal 300 pcs untuk produk reguler.',
                    'status' => 'active', ...$timestamps,
                ]);
                $products[] = ['id' => $id, 'cost' => $cost, 'price' => $cost + 2000];
            }

            for ($d = 89; $d >= 0; $d--) {
                $date = now()->subDays($d)->startOfDay();
                $time = $d === 0 ? now() : $date->copy()->setTime(18, 0);
                $user = $users[$d % 3];
                $register = DB::table('cash_registers')->insertGetId([
                    'user_id' => $user, 'opening_amount' => 200000, 'total_sales' => 0,
                    'total_amount' => 0, 'status' => 'closed', 'opened_at' => $date,
                    'closed_at' => $time, 'created_at' => $date, 'updated_at' => $time,
                ]);
                $cash = 0;
                $cashCount = 0;
                for ($n = 0; $n < 15 + ($d % 6); $n++) {
                    $items = [];
                    $total = 0;
                    for ($j = 0; $j < 1 + ($n % 4); $j++) {
                        $product = $products[($d * 7 + $n * 3 + $j) % 90];
                        $qty = 1 + (($n + $j) % 3);
                        $items[] = ['product' => $product, 'quantity' => $qty];
                        $total += $product['price'] * $qty;
                    }
                    $discount = $n % 5 === 0 ? 1000 : 0;
                    $grand = $total - $discount;
                    $method = $n % 3 === 0 ? 'bank' : 'cash';
                    $customer = $n % 4 === 0 ? null : $customers[($d + $n) % 60];
                    $paid = $method === 'cash' ? (int) (ceil($grand / 10000) * 10000) : $grand;
                    $transaction = DB::table('transactions')->insertGetId([
                        'invoice_number' => 'DEMO-'.$date->format('Ymd').'-'.str_pad((string) $n, 3, '0', STR_PAD_LEFT),
                        'user_id' => $user, 'cash_register_id' => $register, 'customer_id' => $customer,
                        'customer_name' => $customer ? 'DEMO Pelanggan '.((($d + $n) % 60) + 1) : null,
                        'total_price' => $total, 'discount' => $discount, 'tax' => 0,
                        'grand_total' => $grand, 'paid_amount' => $paid, 'change_amount' => $paid - $grand,
                        'status' => 'paid', 'payment_method' => $method, 'created_at' => $time, 'updated_at' => $time,
                    ]);
                    foreach ($items as $item) {
                        DB::table('transaction_items')->insert([
                            'transaction_id' => $transaction, 'product_id' => $item['product']['id'],
                            'quantity' => $item['quantity'], 'price' => $item['product']['price'],
                            'subtotal' => $item['quantity'] * $item['product']['price'], 'unit_name' => 'DEMO pcs',
                            'created_at' => $time, 'updated_at' => $time,
                        ]);
                        DB::table('products')->where('id', $item['product']['id'])->decrement('stock', $item['quantity']);
                    }
                    if ($customer) {
                        DB::table('customers')->where('id', $customer)->increment('points', (int) floor($grand / 1000));
                    }
                    if ($method === 'cash') {
                        $cash += $grand;
                        $cashCount++;
                        DB::table('cash_register_transactions')->insert([
                            'cash_register_id' => $register, 'transaction_id' => $transaction,
                            'created_at' => $time, 'updated_at' => $time,
                        ]);
                    } else {
                        DB::table('bank_transactions')->insert([
                            'cash_register_id' => $register, 'transaction_id' => $transaction, 'amount' => $grand,
                            'created_at' => $time, 'updated_at' => $time,
                        ]);
                    }
                }
                DB::table('cash_registers')->where('id', $register)->update([
                    'total_sales' => $cashCount, 'total_amount' => $cash,
                    'closing_amount' => 200000 + $cash + ($d % 17 === 0 ? -5000 : ($d % 19 === 0 ? 5000 : 0)),
                ]);
                DB::table('expenditures')->insert([
                    'user_id' => $user, 'date' => $date->toDateString(),
                    'amount' => 25000 + ($d % 7) * 10000,
                    'note' => 'DEMO biaya operasional dari dana terpisah, bukan laci kas',
                    'created_at' => $time, 'updated_at' => $time,
                ]);
                if ($d % 3 === 0) {
                    $product = $products[$d % 90];
                    $order = DB::table('purchase_orders')->insertGetId([
                        'supplier_id' => $suppliers[($d % 90) % 10], 'po_number' => 'DEMO-PO-'.$date->format('Ymd'),
                        'order_date' => $date->toDateString(), 'total' => $product['cost'] * 50,
                        'status' => 'completed', 'created_at' => $time, 'updated_at' => $time,
                    ]);
                    DB::table('purchase_order_items')->insert([
                        'purchase_order_id' => $order, 'product_id' => $product['id'], 'quantity' => 50,
                        'price' => $product['cost'], 'subtotal' => $product['cost'] * 50,
                        'created_at' => $time, 'updated_at' => $time,
                    ]);
                    DB::table('products')->where('id', $product['id'])->increment('stock', 50);
                }
            }
            $this->command?->info('DEMO dibuat: 100 produk, 10 supplier, 60 pelanggan, 3 kasir, 1.575 transaksi, 90 kas, 90 pengeluaran, 30 PO.');
        });
    }
}
