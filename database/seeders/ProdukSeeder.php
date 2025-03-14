<?php
use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        Produk::create([
            'id_kategori' => '1',
            'nama_produk' => 'Laptop',
            'harga_beli' => 5000000,
            'harga_jual' => 6000000,
            'stok' => 10,
        ]);

        Produk::create([
            'id_kategori' => '2',
            'nama_produk' => 'Meja',
            'harga_beli' => 200000,
            'harga_jual' => 300000,
            'stok' => 5,
        ]);
    }
}
