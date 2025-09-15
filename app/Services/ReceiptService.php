<?php

namespace App\Services;

class ReceiptService
{
    public static function testPrint(): string
    {
        return "
TOKO KOMERCE
Jl. Jakarta Selatan

*** TEST CETAK ***

Produk   : Indomie Goreng
Qty      : 1
Harga    : 3.500
------------------------
TOTAL    : Rp 3.500

Terima kasih!
";
    }
}
