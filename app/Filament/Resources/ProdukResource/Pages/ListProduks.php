<?php

namespace App\Filament\Resources\ProdukResource\Pages;

use App\Filament\Resources\ProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Http\Livewire\ScanBarcode;
use Filament\Pages\Actions\Action;

class ListProduks extends ListRecords
{
    protected static string $resource = ProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

protected function getActions(): array
{
    return [
        Action::make('scanBarcode')
            ->label('Scan Barcode')
            ->action(function () {
                $this->mountAction('scanBarcode');
            })
            ->modalContent(ScanBarcode::class), // Gunakan komponen Livewire
    ];
}

}
