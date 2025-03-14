<?php

namespace App\Filament\Resources\DetailPenjualanResource\Pages;

use App\Filament\Resources\DetailPenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetailPenjualans extends ListRecords
{
    protected static string $resource = DetailPenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
