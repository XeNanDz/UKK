<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Setting;
use Filament\Notifications\Notification;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class Kasir extends Component
{
    public $cart = [];
    public $search = '';
    public $print_via_mobile = false;
    public $barcode = '';
    public $nama_pelanggan = 'Customer';
    public $pembayaran_id = 0;
    public $pembayaran;
    public $penjualan_items = [];
    public $sub_total = 0;
    public $showConfirmationModal = false;
    public $penjualanToPrint = null;

    protected $listeners = [
        'scanResult' => 'handleScanResult',
    ];

    public function updatedBarcode($barcode)
    {
        $produk = Produk::where('barcode', $barcode)->first();
        if ($produk) {
            if ($produk->stok <= 0) {
                Notification::make()
                    ->title('Stok habis')
                    ->danger()
                    ->send();
                return;
            }

            $existingItemKey = null;
            foreach ($this->penjualan_items as $key => $item) {
                if ($item['produk_id'] == $produk->id) {
                    $existingItemKey = $key;
                    break;
                }
            }

            if ($existingItemKey !== null) {
                $this->penjualan_items[$existingItemKey]['qty']++;
            } else {
                $this->penjualan_items[] = [
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'harga_jual' => $produk->harga_jual,
                    'qty' => 1,
                ];
            }

            session()->put('penjualanItems', $this->penjualan_items);
            $this->barcode = '';
        }
    }

    public function render()
    {
        return view('livewire.kasir', [
            'produks' => Produk::where('stok', '>', 0)
                ->where('nama_produk', 'like', '%' . $this->search . '%')
                ->paginate(15)
        ]);
    }

    public function mount()
    {
        $settings = Setting::first();
        $this->print_via_mobile = $settings->print_via_mobile;

        if (session()->has('penjualanItems')) {
            $this->penjualan_items = session('penjualanItems');
        }
        $this->pembayaran = Pembayaran::all();
    }

    public function addTopenjualan($produkId)
    {
        $produk = Produk::find($produkId);
        if ($produk) {
            if ($produk->stok <= 0) {
                Notification::make()
                    ->title('Stok habis')
                    ->danger()
                    ->send();
                return;
            }

            $existingItemKey = null;
            foreach ($this->penjualan_items as $key => $item) {
                if ($item['produk_id'] == $produkId) {
                    $existingItemKey = $key;
                    break;
                }
            }

            if ($existingItemKey !== null) {
                $this->penjualan_items[$existingItemKey]['qty']++;
            } else {
                $this->penjualan_items[] = [
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'harga_jual' => $produk->harga_jual,
                    'qty' => 1,
                ];
            }

            session()->put('penjualanItems', $this->penjualan_items);
        }
    }

    public function increaseqty($produk_id)
    {
        $produk = Produk::find($produk_id);
        if (!$produk) {
            Notification::make()
                ->title('Produk tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        foreach ($this->penjualan_items as $key => $item) {
            if ($item['produk_id'] == $produk_id) {
                if ($item['qty'] + 1 <= $produk->stok) {
                    $this->penjualan_items[$key]['qty']++;
                } else {
                    Notification::make()
                        ->title('Stok barang tidak mencukupi')
                        ->danger()
                        ->send();
                }
                break;
            }
        }

        session()->put('penjualanItems', $this->penjualan_items);
    }

    public function decreaseqty($produk_id)
    {
        foreach ($this->penjualan_items as $key => $item) {
            if ($item['produk_id'] == $produk_id) {
                if ($this->penjualan_items[$key]['qty'] > 1) {
                    $this->penjualan_items[$key]['qty']--;
                } else {
                    unset($this->penjualan_items[$key]);
                    $this->penjualan_items = array_values($this->penjualan_items);
                }
                break;
            }
        }
        session()->put('penjualanItems', $this->penjualan_items);
    }

    public function calculateTotal()
    {
        $total = 0;
        foreach ($this->penjualan_items as $item) {
            $total += $item['qty'] * $item['harga_jual'];
        }
        $this->sub_total = $total;
        return $total;
    }

    public function resetpenjualan()
    {
        session()->forget(['penjualanItems', 'nama_pelanggan', 'pembayaran_id']);
        $this->penjualan_items = [];
        $this->pembayaran_id = null;
        $this->sub_total = 0;
    }

    public function checkout()
    {
        $this->validate([
            'nama_pelanggan' => 'string|max:255',
            'pembayaran_id' => 'required'
        ]);

        // Pastikan pelanggan_id dan pembayaran_id ada
        if (!$this->pelanggan_id || !$this->pembayaran_id) {
            Notification::make()
                ->title('Pelanggan atau metode pembayaran tidak valid')
                ->danger()
                ->send();
            return;
        }

        $penjualan = Penjualan::create([
            'user_id' => auth()->id(),
            'pelanggan_id' => $this->pelanggan_id,
            'total_harga' => $this->calculateTotal(),
            'pembayaran_id' => $this->pembayaran_id,
            'tanggal_penjualan' => now(),
        ]);

        foreach ($this->penjualan_items as $item) {
            DetailPenjualan::create([
                'penjualan_id' => $penjualan->id,
                'produk_id' => $item['produk_id'],
                'qty' => $item['qty'],
                'harga_jual' => $item['harga_jual'],
                'sub_total' => $item['qty'] * $item['harga_jual'],
            ]);

            // Kurangi stok produk
            $produk = Produk::find($item['produk_id']);
            $produk->stok -= $item['qty'];
            $produk->save();
        }

        $this->penjualanToPrint = $penjualan->id;
        $this->showConfirmationModal = true;

        Notification::make()
            ->title('Penjualan berhasil disimpan')
            ->success()
            ->send();

        $this->resetpenjualan();
    }

    public function confirmPrint1()
    {
        try {
            $penjualan = Penjualan::findOrFail($this->penjualanToPrint);
            $penjualan_items = DetailPenjualan::where('penjualan_id', $penjualan->id)->get();
            $setting = Setting::first();

            $connector = new WindowsPrintConnector($setting->name_printer);
            $printer = new Printer($connector);

            $lineWidth = 32;

            function formatRow($name, $qty, $harga_jual, $lineWidth)
            {
                $nameWidth = 16;
                $qtyWidth = 8;
                $harga_jualWidth = 8;

                $nameLines = str_split($name, $nameWidth);
                $output = '';

                for ($i = 0; $i < count($nameLines) - 1; $i++) {
                    $output .= str_pad($nameLines[$i], $lineWidth) . "\n";
                }

                $lastLine = $nameLines[count($nameLines) - 1];
                $lastLine = str_pad($lastLine, $nameWidth);
                $qty = str_pad($qty, $qtyWidth, " ", STR_PAD_BOTH);
                $harga_jual = str_pad($harga_jual, $harga_jualWidth, " ", STR_PAD_LEFT);

                $output .= $lastLine . $qty . $harga_jual;
                return $output;
            }

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(1, 2);
            $printer->setEmphasis(true);
            $printer->text($setting->shop . "\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            $printer->text($setting->address . "\n");
            $printer->text($setting->phone . "\n");
            $printer->text("================================\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);

            // Periksa apakah relasi pelanggan ada
            if ($penjualan->pelanggan) {
                $printer->text("Nama: " . $penjualan->pelanggan->nama . "\n");
            }

            // Periksa apakah relasi pembayaran ada
            if ($penjualan->pembayaran) {
                $printer->text("Pembayaran: " . $penjualan->pembayaran->metode_pembayaran . "\n");
            }

            $printer->text("Tanggal: " . $penjualan->created_at->format('d-m-Y H:i:s') . "\n");
            $printer->text("================================\n");
            $printer->text(formatRow("Nama Barang", "Qty", "Harga", $lineWidth) . "\n");
            $printer->text("--------------------------------\n");

            foreach ($penjualan_items as $item) {
                $produk = Produk::find($item->produk_id);
                $printer->text(formatRow($produk->nama_produk, $item->qty, number_format($item->harga_jual), $lineWidth) . "\n");
            }

            $printer->text("--------------------------------\n");

            $total = $penjualan->total_harga;
            $printer->setEmphasis(true);
            $printer->text(formatRow("Total", "", number_format($total), $lineWidth) . "\n");
            $printer->setEmphasis(false);

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("================================\n");
            $printer->text("Terima Kasih!\n");
            $printer->text("================================\n");

            $printer->cut();
            $printer->close();

            Notification::make()
                ->title('Struk berhasil dicetak')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Printer tidak terdaftar')
                ->icon('heroicon-o-printer')
                ->danger()
                ->send();
        }

        $this->showConfirmationModal = false;
        $this->penjualanToPrint = null;
    }
}
