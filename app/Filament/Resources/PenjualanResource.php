<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\penjualan;
use App\Models\produk;
use App\Models\Setting;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Mike42\Escpos\Printer;
use Illuminate\Support\Carbon;
use Mike42\Escpos\EscposImage;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use App\Filament\Exports\penjualanExporter;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\penjualanResource\Pages;
use App\Models\detailpenjualan;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class penjualanResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete_any',
        ];
    }
    protected static ?string $model = penjualan::class;



    protected static ?string $navigationIcon = 'heroicon-m-shopping-bag';

    protected static ?string $navigationLabel = 'Penjualan';

    protected static ?string $pluralLabel = 'Penjualan';

    protected static ?string $navigationGroup = 'Menejemen keuangan';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->orderBy('created_at', 'desc');
}


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label('Nama Kasir')
                        ->required(),
                        Forms\Components\Select::make('pelanggan_id')
                        ->relationship('pelanggan', 'nama')
                        ->label('Pelanggan')
                        ->required()
                        ->searchable(),
                    ]),
                Forms\Components\Section::make('Produk dipesan')->schema([
                    self::getItemsRepeater(),
                ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('total_harga')
                            ->required()
                            ->readOnly()
                            ->columnSpan(1)
                            ->numeric(),

                        Forms\Components\Select::make('pembayaran_id')
                            ->relationship('pembayaran', 'metode_pembayaran')
                            ->reactive()
                            ->columnSpan(1)
                            ,
                        Forms\Components\Hidden::make('is_cash')
                            ->dehydrated(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Kasir')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Nama Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->numeric(),
                Tables\Columns\TextColumn::make('pembayaran.metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->numeric(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('Hari Ini')
                ->query(fn ($query) => $query->whereDate('created_at', Carbon::today()))
                ->label('Hari Ini'),
            ])
            ->actions([
                Action::make('Print')
    ->label('Cetak')
    ->hidden(fn () => Setting::first()->value('print_via_mobile')) // Ambil nilai dari model lain
    ->action(function (penjualan $record) {
        try {
            $penjualan = penjualan::findOrFail($record->id);
            $penjualan_items = detailpenjualan::where('penjualan_id', $penjualan->id)->get(); // Perbaiki relasi
            $setting = Setting::first();

            // Sesuaikan nama printer Anda
            $connector = new WindowsPrintConnector($setting->name_printer);
            $printer = new Printer($connector);

            // Lebar kertas (58mm: 32 karakter, 80mm: 48 karakter)
            $lineWidth = 32;

            // Fungsi untuk merapikan teks
            function formatRow($name, $qty, $price, $lineWidth) {
                $nameWidth = 16; // Alokasi 16 karakter untuk nama produk
                $qtyWidth = 8;   // Alokasi 8 karakter untuk Qty
                $priceWidth = 8; // Alokasi 8 karakter untuk Harga

                // Bungkus nama produk jika panjangnya melebihi alokasi
                $nameLines = str_split($name, $nameWidth);

                // Siapkan variabel untuk hasil format
                $output = '';

                // Tambahkan semua baris nama produk kecuali yang terakhir
                for ($i = 0; $i < count($nameLines) - 1; $i++) {
                    $output .= str_pad($nameLines[$i], $lineWidth) . "\n"; // Baris dengan nama saja
                }

                // Baris terakhir dengan Qty dan Harga
                $lastLine = $nameLines[count($nameLines) - 1]; // Baris terakhir dari nama
                $lastLine = str_pad($lastLine, $nameWidth);   // Tambahkan padding untuk nama
                $qty = str_pad($qty, $qtyWidth, " ", STR_PAD_BOTH); // Qty di tengah
                $price = str_pad($price, $priceWidth, " ", STR_PAD_LEFT); // Harga di kanan

                // Gabungkan semua
                $output .= $lastLine . $qty . $price;

                return $output;
            }

            // Header Struk
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(1, 2);
            $printer->setEmphasis(true); // Tebal
            $printer->text($setting->shop . "\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false); // Tebal
            $printer->text($setting->address . "\n");
            $printer->text($setting->phone . "\n");
            $printer->text("================================\n");

            // Detail Transaksi
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            if ($penjualan->pelanggan) {
                $printer->text("Nama Pelanggan: " . $penjualan->pelanggan->nama . "\n");
            }
            if ($penjualan->pembayaran) {
                $printer->text("Metode Pembayaran: " . $penjualan->pembayaran->metode_pembayaran . "\n");
            }
            $printer->text("Tanggal: " . $penjualan->created_at->format('d-m-Y H:i:s') . "\n");
            $printer->text("================================\n");
            $printer->text(formatRow("Nama Barang", "Qty", "Harga", $lineWidth) . "\n");
            $printer->text("--------------------------------\n");

            // Detail Produk
            foreach ($penjualan_items as $item) {
                $produk = produk::find($item->produk_id);
                $printer->text(formatRow(
                    $produk->nama_produk,
                    $item->qty,
                    number_format($item->harga_jual),
                    $lineWidth
                ) . "\n");
            }

            $printer->text("--------------------------------\n");

            // Total Harga
            $total = $penjualan->total_harga;
            $printer->setEmphasis(true); // Tebal
            $printer->text(formatRow("Total", "", number_format($total), $lineWidth) . "\n");
            $printer->setEmphasis(false); // Tebal

            // Footer Struk
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("================================\n");
            $printer->text("Terima Kasih!\n");
            $printer->text("================================\n");

            $printer->cut();
            $printer->close();

            Notification::make()
                ->title('Struk berhasil dicetak')
                ->success()
                ->icon('heroicon-o-printer')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Printer tidak terdaftar')
                ->icon('heroicon-o-printer')
                ->danger()
                ->send();
        }
    })
    ->icon('heroicon-o-printer')
    ->color('amber'),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(penjualanExporter::class),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ExportAction::make()->exporter(penjualanExporter::class),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\Listpenjualans::route('/'),
            'create' => Pages\Createpenjualan::route('/create'),
            'edit' => Pages\Editpenjualan::route('/{record}/edit'),
        ];
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('detailpenjualans')
            ->relationship()
            ->live()
            ->columns([
                'md' => 10,
            ])
            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                self::updateTotalPrice($get, $set);
            })
            ->schema([
                Forms\Components\Select::make('produk_id')
                    ->label('Produk')
                    ->required()
                    ->options(produk::query()->where('stok', '>', 1)->pluck('nama_produk', 'id'))
                    ->columnSpan([
                        'md' => 5
                    ])
                    ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state) {
                        $produk = produk::find($state);
                        $set('unit_price', $produk->price ?? 0);
                        $set('stock', $produk->stock ?? 0);
                    })
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $produk = produk::find($state);
                        $set('unit_price', $produk->price ?? 0);
                        $set('stock', $produk->stock ?? 0);
                        $quantity = $get('quantity') ?? 1;
                        $stock = $get('stock');
                        self::updateTotalPrice($get, $set);
                    })
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->columnSpan([
                        'md' => 1
                    ])
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $stock = $get('stock');
                        if ($state > $stock) {
                            $set('quantity', $stock);
                            Notification::make()
                                ->title('Stok tidak mencukupi')
                                ->warning()
                                ->send();
                        }

                        self::updateTotalPrice($get, $set);
                    }),
                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->readOnly()
                    ->columnSpan([
                        'md' => 1
                    ]),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Harga saat ini')
                    ->required()
                    ->numeric()
                    ->readOnly()
                    ->columnSpan([
                        'md' => 3
                    ]),

            ]);
    }

    protected static function updateTotalPrice(Forms\Get $get, Forms\Set $set): void
    {
        $selectedproduks = collect($get('detailpenjualans'))->filter(fn($item) => !empty($item['produk_id']) && !empty($item['quantity']));

        $prices = produk::find($selectedproduks->pluck('produk_id'))->pluck('price', 'id');
        $total = $selectedproduks->reduce(function ($total, $produk) use ($prices) {
            return $total + ($prices[$produk['produk_id']] * $produk['quantity']);
        }, 0);

        $set('total_price', $total);
    }

    protected static function updateExcangePaid(Forms\Get $get, Forms\Set $set): void
    {
        $paidAmount = (int) $get('paid_amount') ?? 0;
        $totalPrice = (int) $get('total_price') ?? 0;
        $exchangePaid = $paidAmount - $totalPrice;
        $set('change_amount', $exchangePaid);
    }
}
