<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <h1>Laporan Penjualan</h1>
    <table>
        <thead>
            <tr>
                <th>ID Penjualan</th>
                <th>Kasir</th>
                <th>Pelanggan</th>
                <th>Metode Pembayaran</th>
                <th>Total Harga</th>
                <th>Diskon</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $record->id }}</td>
                <td>{{ $record->user->name }}</td>
                <td>{{ $record->pelanggan->nama }}</td>
                <td>{{ $record->pembayaran->metode_pembayaran }}</td>
                <td>Rp {{ number_format($record->total_harga, 0, ',', '.') }}</td>
                <td>{{ $record->diskon }}%</td>
                <td>{{ $record->created_at->format('d M Y H:i') }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Detail Penjualan</h2>
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
            @foreach ($detailpenjualan as $item)
                <tr>
                    <td>{{ $item->produk->nama_produk }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->qty * $item->harga_jual, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
