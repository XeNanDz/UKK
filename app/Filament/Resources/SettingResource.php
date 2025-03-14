<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;


class SettingResource extends Resource implements HasShieldPermissions
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

    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-printer';

    protected static ?string $navigationLabel = 'Pengaturan Printer';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationGroup = 'Pengaturan Toko';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('shop')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Toko'),
                Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255)
                    ->label('Alamat Toko'),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(255)
                    ->label('Nomor Telepon'),
                Forms\Components\TextInput::make('name_printer')
                    ->maxLength(255)
                    ->label('Nama Printer')
                    ->helperText('Samakan dengan nama printer yang anda gunakan dan sudah terdaftar atau terhubung. Contoh: Epson T20')
                    ->hidden(fn (Setting $record) => $record->print_via_mobile) // Disembunyikan jika print_via_mobile bernilai true
                    ->dehydrated(fn (Setting $record): bool => $record->print_via_mobile === 0),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->required()
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('200')
                    ->imageResizeTargetHeight('200')
                    ->imageEditor() // Folder untuk menyimpan file
                    ->maxSize(1024 * 1024)
                    ->helperText('Pastikan format gambar adalah PNG')
                    ->directory('images')
                    ->label('Logo Toko'),
                Forms\Components\Toggle::make('print_via_mobile')
                    ->required()
                    ->label('Print Via Mobile')
                    ->helperText('Ini akan memunculkan halaman untuk menghandle print pada mobile menggunakan fitur print pada browser'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular()
                    ->label('Logo Toko'),
                Tables\Columns\TextColumn::make('shop')
                    ->label('Nama Toko')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat Toko')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Nomor Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_printer')
                    ->label('Nama Printer')
                    ->searchable(),
                Tables\Columns\IconColumn::make('print_via_mobile')
                    ->label('Print Via Mobile')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return Setting::count() < 1;
    }
}
