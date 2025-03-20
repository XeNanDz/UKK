<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use Barryvdh\DomPDF\Facade\Pdf;

class StrukController extends Controller
{
    public function generateStruk($id)
    {
        // Ambil data penjualan
        $penjualan = Penjualan::with(['detailPenjualan.produk'])->find($id);

        if (!$penjualan) {
            return redirect()->back()->with('error', 'Penjualan tidak ditemukan');
        }

        // Generate PDF
        $pdf = Pdf::loadView('kasir.struk', ['penjualan' => $penjualan]);

        // Download PDF
        return $pdf->download('struk-penjualan-' . $penjualan->id . '.pdf');
    }
}
