<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Return {{ $retur->return_number }}</title>
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
                Purchase Return <br>
                <small>No: {{ $retur->return_number }}</small>
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
                <strong>Supplier</strong><br>
                {{ $retur->supplier->name }}<br>
                {{ $retur->supplier->address }}<br>
                Telp: {{ $retur->supplier->contact }}<br>
                Email: {{ $retur->supplier->email }}
            </td>
        </tr>
    </table>

    <!-- Detail Retur -->
    <table>
        <tr>
            <td><strong>Tanggal:</strong> {{ $retur->return_date }}</td>
            <td><strong>Reason:</strong> {{ $retur->reason ?? '-' }}</td>
        </tr>
    </table>

    <!-- Items -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retur->items as $i => $item)
            <tr>
                <td style="text-align: center">{{ $i+1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td style="text-align: center">
                    {{ $item->quantity }}
                    {{ $item->unitConversion->unit_name ?? $item->product->unit->name }}
                </td>
                <td style="text-align: right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td style="text-align: right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                <td>{{ $item->note ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total -->
    <p style="text-align: right; font-weight: bold; margin-top: 10px;">
        Total: Rp {{ number_format($retur->total, 0, ',', '.') }}
    </p>

    <!-- Footer -->
    <div style="margin-top:40px;">
        <p style="text-align:right;">Hormat Kami,</p>
        <br><br><br>
        <p style="text-align:right;">(___________________)</p>
    </div>
</body>
</html>
