<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Mengubah tipe kolom untuk mengakomodasi status baru
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Lunas', 'Belum Lunas', 'Nonaktif') NOT NULL DEFAULT 'Belum Lunas'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Mengembalikan ke kondisi semula jika migrasi di-rollback
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Lunas', 'Belum Lunas') NOT NULL DEFAULT 'Belum Lunas'");
    }
};
