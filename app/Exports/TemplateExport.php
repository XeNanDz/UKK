<?php

namespace App\Exports;

use App\Models\kategori;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class TemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ProduksExport(),
            new KategorisExport()
        ];
    }
}

class ProduksExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return collect([]);
    }

    public function headings(): array
    {
        return [
            'nama_produk',
            'kategori_id',
            'stok',
            'harga_beli',
            'harga_jual',
            'is_active',
            'barcode',
            'image'
        ];
    }

    public function title(): string
    {
        return 'Produk';
    }
}

class KategorisExport implements FromCollection, WithHeadings, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return kategori::select('id', 'nama_kategori')->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'nama_kategori'
        ];
    }

    public function title(): string
    {
        return 'kategori';
    }
}
