<?php

namespace App\Filament\Resources\DetailPenjualanResource\Pages;

use App\Filament\Resources\DetailPenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDetailPenjualan extends EditRecord
{
    protected static string $resource = DetailPenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
