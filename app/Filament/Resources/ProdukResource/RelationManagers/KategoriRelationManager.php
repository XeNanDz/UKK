<?php

namespace App\Filament\Resources\ProdukResource\RelationManagers;

use App\Models\kategori;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class KategoriRelationManager extends RelationManager
{
    protected static string $relationship = 'kategori';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('id_kategori')
                    ->label('Pilih Kategori')
                    ->options(kategori::all()->pluck('nama_kategori', 'id_kategori'))
                    ->searchable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_kategori'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
