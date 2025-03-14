<?php

namespace App\Filament\Pages;
use Illuminate\Support\Facades\Auth;
use Filament\Pages\Page;
use Spatie\Permission\Traits\HasRoles;
use Filament\Support\Enums\MaxWidth;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class KasirPage extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.kasir-page';

    protected static ?string $slug = 'kasir';

    protected static ?string $title = 'Halaman Kasir';


    public function getMaxContentWidth(): MaxWidth
{
    return MaxWidth::Full;
}


}
