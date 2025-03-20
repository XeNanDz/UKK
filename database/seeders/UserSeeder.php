<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data pengguna yang akan di-seed
        $users = [
            [
                'name' => 'Admin',
                'email' => '123@gg.com',
                'hp' => '081234567890',
                'status' => 'active',
                'password' => Hash::make('123456'), // Password di-hash menggunakan bcrypt
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Masukkan data ke tabel users
        DB::table('users')->insert($users);
    }
}
