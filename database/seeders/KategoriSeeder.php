<?php
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        Kategori::create([
            'id_kategori' => '1',
            'nama_kategori' => 'Elektronik',
        ]);

        Kategori::create([
            'id_kategori' => '2',
            'nama_kategori' => 'Furnitur',
        ]);
    }
}
