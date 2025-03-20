<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'shop' => 'Toko Kelontong',
            'address' => 'Jl. Contoh No. 123',
            'phone' => '08123456789',
            'name_printer' => 'Microsoft Print to PDF',
            'image' => 'path/to/image.png',
            'print_via_mobile' => false,
        ]);
    }
}
