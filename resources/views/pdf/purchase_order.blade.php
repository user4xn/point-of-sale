<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $po->po_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background: #f0f0f0; text-align: center; }
        .header, .footer { width: 100%; margin-bottom: 10px; }
        .header td { border: none; padding: 2px; }
        .title { font-size: 16px; font-weight: bold; text-align: right; }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header">
        <tr>
            <td>
                <p><strong>{{ $setting->store_name }}</strong></p>
                <p>{{ $setting->store_address }}</p>
                <p>Telp: {{ $setting->store_contact }}</p>
            </td>
            <td class="title">
                Purchase Order <br>
                <small>No: {{ $po->po_number }}</small>
            </td>
        </tr>
    </table>

    <!-- Info Perusahaan & Supplier -->
    <table class="header">
        <tr>
            <td width="50%">
                <strong>Info Perusahaan</strong><br>
                {{ $setting->store_name }}<br>
                {{ $setting->store_address }}<br>
                Telp: {{ $setting->store_contact }}<br>
            </td>
            <td width="50%">
                <strong>Order Ke (Supplier)</strong><br>
                {{ $po->supplier->name }}<br>
                {{ $po->supplier->address }}<br>
                Telp: {{ $po->supplier->contact }}<br>
                Email: {{ $po->supplier->email }}
            </td>
        </tr>
    </table>

    <!-- Detail PO -->
    <table>
        <tr>
            <td><strong>Tanggal:</strong> {{ $po->order_date }}</td>
        </tr>
    </table>

    <!-- Items -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Kuantitas</th>
            </tr>
        </thead>
        <tbody>
            @php
              $i = 1;
            @endphp
            @foreach($po->items as $item)
            <tr> 
                <td style="text-align: center">{{ $i++ }}.</td>
                <td>{{ $item->product->name }}</td>
                <td style="text-align:center;">{{ $item->quantity }} {{ $item->unitConversion->unit_name ?? $item->product->unit->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div style="margin-top:40px;">
        <br><br>
        <p style="text-align:right;">Hormat Kami,</p>
        <br><br><br>
        <p style="text-align:right;">(___________________)</p>
    </div>
</body>
</html>
