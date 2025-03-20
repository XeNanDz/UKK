<?php
namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class PenjualanController extends Controller
{
    /**
     * Menampilkan daftar penjualan.
     */
    public function index()
    {
        $penjualans = Penjualan::with(['user', 'pelanggan', 'pembayaran', 'detailpenjualan.produk'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('penjualan.index', compact('penjualans'));
    }

    /**
     * Menampilkan detail penjualan.
     */
    public function show($id)
    {
        $penjualan = Penjualan::with(['user', 'pelanggan', 'pembayaran', 'detailpenjualan.produk'])
            ->findOrFail($id);

        return view('penjualan.show', compact('penjualan'));
    }

    /**
     * Mengekspor data penjualan ke PDF.
     */
    public function exportPdf($id)
    {
        try {
            // Ambil data penjualan berdasarkan ID
            $penjualan = Penjualan::with(['user', 'pelanggan', 'pembayaran', 'detailpenjualan.produk'])
                ->findOrFail($id);

            // Ambil data detail penjualan
            $detailpenjualan = DetailPenjualan::where('penjualan_id', $id)->get();

            // Render view PDF dengan data yang diperlukan
            $html = Blade::render('exports.penjualan_pdf', [
                'penjualan' => $penjualan, // Data penjualan
                'detailpenjualan' => $detailpenjualan, // Data detail penjualan
            ]);

            // Generate PDF
            $pdf = Pdf::loadHtml($html);

            // Download PDF
            return $pdf->download('penjualan_' . $penjualan->id . '.pdf');
        } catch (\Exception $e) {
            // Tangani error
            return redirect()->back()->with('error', 'Gagal mengekspor PDF: ' . $e->getMessage());
        }
    }
}
