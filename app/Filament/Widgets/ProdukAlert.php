<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\produk;

class produkAlert extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Status Stok Produk';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                produk::query()->orderBy('stok', 'asc')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                ->label('Gambar')
                ->circular(),
                Tables\Columns\TextColumn::make('nama_produk')
                    ->label('Nama Produk')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('stok_status')
                    ->label('Status')
                    ->getStateUsing(static function ($record): string {
                        if ($record->stok <= 0) {
                            return 'Habis';
                        } elseif ($record->stok < 10) {
                            return 'Hampir Habis';
                        }
                        return 'Aman';
                    })
                    ->color(static function ($state): string {
                        return match ($state) {
                            'Habis' => 'danger',
                            'Hampir Habis' => 'warning',
                            'Aman' => 'success',
                            default => 'secondary',
                        };
                    }),
                Tables\Columns\BadgeColumn::make('stok')
                    ->label('Stok')
                    ->numeric()
                    ->color(static function ($state): string {
                        if ($state <= 0) {
                            return 'danger';
                        } elseif ($state < 10) {
                            return 'warning';
                        }
                        return 'success';
                    })
                    ->sortable(),

            ])
            ->defaultPaginationPageOption(5);
    }
}
