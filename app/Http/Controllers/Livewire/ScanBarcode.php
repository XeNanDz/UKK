<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Produk;

class ScanBarcode extends Component
{
    public $barcode;
    public $produk;

    public function render()
    {
        return view('livewire.scan-barcode');
    }

    public function scanBarcode()
    {
        $this->validate([
            'barcode' => 'required|string',
        ]);

        $this->produk = Produk::where('barcode', $this->barcode)->first();

        if (!$this->produk) {
            session()->flash('error', 'Produk tidak ditemukan.');
        }
    }
}
