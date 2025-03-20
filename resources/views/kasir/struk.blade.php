<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .struk {
            width: 300px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
        }
        .detail {
            margin-bottom: 10px;
        }
        .detail table {
            width: 100%;
            border-collapse: collapse;
        }
        .detail table th,
        .detail table td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: left;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="struk">
        <div class="header">
            <h2>TRIAL SID RETAIL PRO</h2>
            <p>NPMP 14.176.118.9-735.000</p>
            <p>SID RETAIL DEMO</p>
        </div>
        <div class="detail">
            <p>MASTER R43-020118003  {{ $penjualan->created_at->format('d/m/y H:i:s') }}</p>
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($penjualan->detailPenjualans)
                        @foreach ($penjualan->detailPenjualans as $item)
                            <tr>
                                <td>{{ $item->produk->nama_produk }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                <td>{{ number_format($item->sub_total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada detail penjualan</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="total">
            <p>Total Harga: Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</p>
            @if ($penjualan->diskon > 0)
                <p>Diskon Global: {{ $penjualan->diskon }}%</p>
                <p>Total Setelah Diskon: Rp {{ number_format($penjualan->total_harga - ($penjualan->total_harga * ($penjualan->diskon / 100)), 0, ',', '.') }}</p>
            @endif
            <p>Uang Pembayaran: Rp {{ number_format($penjualan->uang_pembayaran, 0, ',', '.') }}</p>
            <p>Kembalian: Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}</p>
        </div>
        <div class="footer">
            <p>TERIMA KASIH PELANGGAN SETIA</p>
            <p>KAMI TUNGGU ANDA UNTUK BELANJA KEMBALI</p>
        </div>
    </div>
</body>
</html>
