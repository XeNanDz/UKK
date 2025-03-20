<?php

namespace App\Livewire;

use App\Models\Produk;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Pembayaran;
use Filament\Forms\Components\Grid;
use Mike42\Escpos\Printer;
use Filament\Forms\Components\Select;
use App\Models\DetailPenjualan;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Forms\Set;
use Filament\Pages\Page;

class Kasir extends Component implements HasForms
{
    use InteractsWithForms;

    public $search = '';
    public $print_via_mobile = false;
    public $barcode = '';
    public $pelanggan_id = null;
    public $pembayaran_id = 0;
    public $pembayaran;
    public $penjualan_items = [];
    public $total_harga = 0;
    public $showConfirmationModal = false;
    public $penjualanToPrint = null;
    public $diskon = 0; // Diskon global dalam persentase
    public $uang_pembayaran = 0;
    public $kembalian = 0;

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
                    'diskon' => 0, // Diskon per barang default 0
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        Select::make('pelanggan_id')
                            ->label('Pelanggan')
                            ->options(Pelanggan::pluck('nama', 'id'))
                            ->columnSpan(1),
                        Select::make('pembayaran_id')
                            ->required()
                            ->label('Metode Pembayaran')
                            ->options(Pembayaran::pluck('metode_pembayaran', 'id'))
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('uang_pembayaran')
                            ->label('Uang Pembayaran (Rp)')
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $total = $this->calculateTotal();
                                $kembalian = $state - $total;
                                $set('kembalian', $kembalian);
                            }),
                        Forms\Components\TextInput::make('diskon')
                            ->label('Diskon Global (%)')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $total = $this->calculateTotal();
                                $kembalian = $this->uang_pembayaran - $total;
                                $set('kembalian', $kembalian);
                            }),
                        Forms\Components\TextInput::make('kembalian')
                            ->label('Kembalian (Rp)')
                            ->numeric()
                            ->disabled(),
                    ]),
                Forms\Components\Repeater::make('penjualan_items')
                    ->schema([
                        Forms\Components\TextInput::make('diskon')
                            ->label('Diskon per Barang (%)')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $total = $this->calculateTotal();
                                $kembalian = $this->uang_pembayaran - $total;
                                $set('kembalian', $kembalian);
                            }),
                    ]),
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

    public function addToPenjualan($produkId)
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
                    'diskon' => 0, // Diskon per barang default 0
                ];
            }

            session()->put('penjualanItems', $this->penjualan_items);
        }
    }

    public function increaseQty($produk_id)
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

    public function decreaseQty($produk_id)
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
            $qty = (int)$item['qty'];
            $harga_jual = (float)$item['harga_jual'];
            $diskon = (float)$item['diskon']; // Ambil diskon per barang

            // Hitung subtotal dengan diskon per barang
            $subtotal = $qty * $harga_jual;
            $subtotal -= $subtotal * ($diskon / 100);

            $total += $subtotal;
        }

        // Hitung diskon global (jika ada)
        $diskon_global = (float)$this->diskon;
        $total -= $total * ($diskon_global / 100);

        $this->total_harga = $total;
        return $this->total_harga;
    }

    public function resetPenjualan()
    {
        session()->forget(['penjualanItems', 'pelanggan_id', 'pembayaran_id']);
        $this->penjualan_items = [];
        $this->pembayaran_id = null;
        $this->diskon = 0;
        $this->uang_pembayaran = 0;
        $this->kembalian = 0;
        $this->total_harga = 0;
    }

    public function calculateKembalian()
    {
        $total = $this->calculateTotal();
        return $this->uang_pembayaran - $total;
    }

    public function checkout()
    {
        // Validasi data
        $this->validate([
            'pelanggan_id' => 'nullable|exists:pelanggans,id',
            'pembayaran_id' => 'required|exists:pembayarans,id',
            'uang_pembayaran' => 'required|numeric|min:' . $this->calculateTotal(),
        ]);

        try {
            // Simpan data penjualan
            $penjualan = Penjualan::create([
                'user_id' => auth()->id(),
                'pelanggan_id' => $this->pelanggan_id,
                'total_harga' => $this->calculateTotal(),
                'pembayaran_id' => $this->pembayaran_id,
                'diskon' => $this->diskon,
                'uang_pembayaran' => $this->uang_pembayaran,
                'kembalian' => $this->kembalian,
                'created_at' => now(),
            ]);

            // Simpan detail penjualan
            foreach ($this->penjualan_items as $item) {
                DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $item['produk_id'],
                    'qty' => $item['qty'],
                    'harga_jual' => $item['harga_jual'],
                    'diskon' => $item['diskon'], // Simpan diskon per barang
                    'sub_total' => $item['qty'] * $item['harga_jual'] * (1 - ($item['diskon'] / 100)),
                ]);

                // Kurangi stok produk
                $produk = Produk::find($item['produk_id']);
                $produk->stok -= $item['qty'];
                $produk->save();
            }

            // Tampilkan notifikasi sukses
            Notification::make()
                ->title('Penjualan berhasil disimpan')
                ->success()
                ->send();

            // Reset form
            $this->resetPenjualan();

            // Set penjualanToPrint untuk modal konfirmasi
            $this->penjualanToPrint = $penjualan->id;
            $this->showConfirmationModal = true;

        } catch (\Exception $e) {
            // Tampilkan notifikasi error
            Notification::make()
                ->title('Gagal menyimpan penjualan')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function printStruk($id)
    {
        // Redirect ke route cetak struk
        return redirect()->route('kasir.printStruk', $id);
    }
}
