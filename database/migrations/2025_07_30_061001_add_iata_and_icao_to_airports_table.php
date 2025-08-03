<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('airports', function (Blueprint $table) {
            // Langkah 1: Cek apakah kolom 'code' ada, jika ya, ubah namanya.
            if (Schema::hasColumn('airports', 'code')) {
                $table->renameColumn('code', 'iata_code');
            }

            // Langkah 2: Cek apakah kolom 'icao_code' belum ada, jika belum, tambahkan.
            if (!Schema::hasColumn('airports', 'icao_code')) {
                $table->string('icao_code', 4)->after('iata_code')->nullable()->unique();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('airports', function (Blueprint $table) {
            // Logika untuk membatalkan migrasi (rollback) yang aman
            if (Schema::hasColumn('airports', 'icao_code')) {
                $table->dropColumn('icao_code');
            }
            if (Schema::hasColumn('airports', 'iata_code')) {
                $table->renameColumn('iata_code', 'code');
            }
        });
    }
};
