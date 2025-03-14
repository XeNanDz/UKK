<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukResource\Pages;
use App\Filament\Resources\ProdukResource\RelationManagers;
use App\Filament\Resources\ProdukResource\RelationManagers\KategoriRelationManager;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\produks;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdukResource extends Resource
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
    protected static ?string $model = produk::class;

    protected static ?string $navigationIcon = 'heroicon-m-square-3-stack-3d';

    protected static ?string $navigationLabel = 'Produk';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Menejemen Produk';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_produk')
                ->label('Nama Produk')
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('slug', Produk::generateUniqueSlug($state));
                    }),
                Forms\Components\Select::make('kategori_id')
                ->label('Kategori Produk')
                ->relationship('kategori', 'nama_kategori') // Relasi ke tabel kategori
                ->required(),
                TextInput::make('slug')
                    ->required()
                    ->readOnly()
                    ->maxLength(255),
                TextInput::make('harga_beli')
                ->label('Harga Beli')
                    ->required()
                    ->numeric()
                    ->prefix('Rp.'),
                TextInput::make('harga_jual')
                ->label('Harga Jual')
                    ->required()
                    ->numeric()
                    ->prefix('Rp.'),
                TextInput::make('stok')
                ->label('Stok Produk')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\Toggle::make('is_active')
                    ->label('Produk Aktif')
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->label('Gambar Produk')
                    ->image()
                    ->maxSize(1024),
                TextInput::make('barcode')
                ->label('Barcode')
                ->required()
                ->unique('produks', 'barcode', ignoreRecord: true),
            Forms\Components\Fieldset::make('Barcode Preview')
                ->schema([
                    Forms\Components\ViewField::make('barcode-preview')
                        ->view('filament.forms.components.barcode-preview') // Buat view untuk preview barcode
                        ->hidden(fn ($get) => !$get('barcode')),
                ]),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('nama_produk')
                ->label('Nama Produk')
                ->searchable(),
            Tables\Columns\ImageColumn::make('image')
                ->label('Gambar')
                ->circular(),
            TextColumn::make('kategori.nama_kategori')
                ->label('Kategori')
                ->searchable(), // Menampilkan nama kategori dari relasi
            TextColumn::make('harga_beli')
                ->label('Harga Beli')
                ->sortable(),
            TextColumn::make('harga_jual')
                ->label('Harga Jual')
                ->sortable(),
            TextColumn::make('stok')
                ->label('Stok')
                ->numeric()
                ->searchable(),
                TextColumn::make('barcode')
                ->label('Barcode')
                ->getStateUsing(function ($record) {
                    return $record->generateBarcode(); // Generate barcode
                })
                ->html(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),



            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            KategoriRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProduks::route('/'),
            'create' => Pages\CreateProduk::route('/create'),
            'edit' => Pages\EditProduk::route('/{record}/edit'),
        ];
    }
}
