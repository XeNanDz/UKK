<?php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Produk;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;

class Kasir extends Component
{
    public $barcode;
    public $produk;
    public $qty = 1;
    public $diskon = 0;
    public $totalHarga = 0;

    public function updatedBarcode($value)
    {
        $this->produk = Produk::where('barcode', $value)->first();

        if (!$this->produk) {
            session()->flash('error', 'Produk tidak ditemukan');
        }
    }

    public function simpanTransaksi()
    {
        $this->validate([
            'produk' => 'required',
            'qty' => 'required|numeric|min:1',
            'diskon' => 'nullable|numeric|min:0',
        ]);

        // Hitung total harga
        $this->totalHarga = $this->produk->harga_jual * $this->qty;
        $this->totalHarga -= $this->totalHarga * ($this->diskon / 100);

        // Simpan transaksi
        $penjualan = Penjualan::create([
            'id_pelanggan' => 1, // Default pelanggan
            'diskon' => $this->diskon,
            'total_harga' => $this->totalHarga,
            'tanggal_penjualan' => now(),
        ]);

        DetailPenjualan::create([
            'id_penjualan' => $penjualan->id_penjualan,
            'id_produk' => $this->produk->id_produk,
            'harga_jual' => $this->produk->harga_jual,
            'qty' => $this->qty,
            'sub_total' => $this->totalHarga,
            'tanggal_penjualan' => now(),
        ]);

        // Reset form
        $this->reset(['barcode', 'produk', 'qty', 'diskon', 'totalHarga']);
        session()->flash('success', 'Transaksi berhasil disimpan');
    }

    public function render()
    {
        return view('livewire.kasir');
    }
}
