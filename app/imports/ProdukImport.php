<?php

namespace App\imports;

use App\Models\Produk;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;

class produkImport implements ToModel, WithHeadingRow, WithMultipleSheets, SkipsEmptyRows
{

    public function sheets(): array
    {
        return [
            0 => $this
        ];
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        return new produk([
            'nama_produk' => $row['nama_produk'],
            'slug' => produk::generateUniqueSlug($row['nama_produk']),
            'id_kategori' => $row['id_kategori'],
            'stok' => $row['stok'],
            'harga_beli' => $row['harga_beli'],
            'harga_jual' => $row['harga_jual'],
            'is_active' => $row['is_active'],
            'barcode' => $row['barcode'],
            'image' => $row['image']

        ]);
    }
}
