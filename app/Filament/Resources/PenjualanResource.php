<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Forms;
use Filament\Tables;
use App\Models\Produk;
use App\Models\Setting;
use Filament\Forms\Form;
use App\Models\Penjualan;
use Filament\Tables\Table;
use App\Models\DetailPenjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Resources\Resource;
use App\Exports\PenjualanPdfExport;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\PenjualanExporter;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\PenjualanResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class PenjualanResource extends Resource implements HasShieldPermissions
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

    protected static ?string $model = Penjualan::class;

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
                            ->columnSpan(1),
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
                    Tables\Columns\TextColumn::make('diskon')
                    ->label('Diskon')
                    ->numeric(),
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->numeric(),
                Tables\Columns\TextColumn::make('pembayaran.metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('Hari Ini')
                    ->query(fn ($query) => $query->whereDate('created_at', Carbon::today()))
                    ->label('Hari Ini'),
                    Filter::make('1 Minggu Terakhir')
                    ->query(fn ($query) =>$query->whereBetween('created_at', [Carbon::now()->subWeek(), Carbon::now()]))
                    ->label('1 Minggu Terakhir'),
                    Filter::make('1 Bulan Terakhir')
                    ->query(fn ($query) =>$query->whereBetween('created_at', [Carbon::now()->subMonth(), Carbon::now()]))
                    ->label('1 Bulan Terakhir'),
                    Filter::make('1 Tahun Terakhir')
                    ->query(fn ($query) => $query->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()]))
                    ->label('1 Tahun Terakhir'),

            ])
            ->actions([
                Action::make('pdf')
    ->label('Export PDF')
    ->icon('heroicon-o-document-arrow-down')
    ->openUrlInNewTab()
    ->color('success')
    ->action(function ($record) { // $record adalah data penjualan yang dipilih
        try {
            // Ambil data detail penjualan berdasarkan penjualan_id
            $detailpenjualan = DetailPenjualan::where('penjualan_id', $record->id)->get();

            // Render view PDF dengan data yang diperlukan
            $html = Blade::render('exports.penjualan_pdf', [
                'record' => $record, // Data penjualan
                'detailpenjualan' => $detailpenjualan, // Data detail penjualan
            ]);

            // Generate PDF
            $pdf = Pdf::loadHtml($html);

            // Download PDF
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->stream();
            }, 'penjualan_' . $record->id . '.pdf');
        } catch (\Exception $e) {
            // Tangani error
            Notification::make()
                ->title('Gagal mengekspor PDF')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();

        }
    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(PenjualanExporter::class),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ExportAction::make()->exporter(PenjualanExporter::class),
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
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'edit' => Pages\EditPenjualan::route('/{record}/edit'),
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
                    ->options(Produk::query()->where('stok', '>', 1)->pluck('nama_produk', 'id'))
                    ->columnSpan([
                        'md' => 5
                    ])
                    ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state) {
                        $produk = Produk::find($state);
                        $set('unit_price', $produk->price ?? 0);
                        $set('stok', $produk->stok ?? 0);
                    })
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $produk = Produk::find($state);
                        $set('unit_price', $produk->price ?? 0);
                        $set('stok', $produk->stok ?? 0);
                        $qty = $get('qty') ?? 1;
                        $stok = $get('stok');
                        self::updateTotalPrice($get, $set);
                    })
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                Forms\Components\TextInput::make('qty')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->columnSpan([
                        'md' => 1
                    ])
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $stok = $get('stok');
                        if ($state > $stok) {
                            $set('qty', $stok);
                            Notification::make()
                                ->title('Stok tidak mencukupi')
                                ->warning()
                                ->send();
                        }

                        self::updateTotalPrice($get, $set);
                    }),
                Forms\Components\TextInput::make('stok')
                    ->required()
                    ->numeric()
                    ->readOnly()
                    ->columnSpan([
                        'md' => 1
                    ]),
                Forms\Components\TextInput::make('harga_jual')
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
        $selectedProduks = collect($get('detailpenjualans'))->filter(fn($item) => !empty($item['produk_id']) && !empty($item['qty']));

        $prices = Produk::find($selectedProduks->pluck('produk_id'))->pluck('price', 'id');
        $total = $selectedProduks->reduce(function ($total, $produk) use ($prices) {
            return $total + ($prices[$produk['produk_id']] * $produk['qty']);
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
