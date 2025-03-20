<?php
namespace App\Http\Controllers;

use App\Models\Penjualan;
use Barryvdh\DomPDF\Facade\Pdf;

class KasirController extends Controller
{
    public function printStruk($id)
    {
        // Ambil data penjualan berdasarkan ID
        $penjualan = Penjualan::findOrFail($id);

        // Generate PDF
        $pdf = Pdf::loadView('kasir.struk', compact('penjualan'));

        // Buka PDF di browser
        return $pdf->stream('struk_penjualan.pdf');
    }
}
