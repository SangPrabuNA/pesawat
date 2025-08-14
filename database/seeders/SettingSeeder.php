<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Pastikan ini di-import

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Masukkan nilai kurs USD awal
        DB::table('settings')->insert([
            'key' => 'usd_exchange_rate',
            'value' => '15000', // Anda bisa ganti nilai default ini
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
