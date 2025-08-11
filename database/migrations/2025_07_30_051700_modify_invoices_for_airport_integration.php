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
        Schema::table('invoices', function (Blueprint $table) {
            // Menambahkan foreign key yang boleh null (nullable) untuk menghindari error pada tabel yang sudah ada isinya.
            $table->foreignId('airport_id')->after('id')->nullable()->constrained()->onDelete('cascade');

            // Menambahkan kolom baru
            $table->string('ground_handling')->nullable()->after('airline');
            $table->string('flight_number_2')->nullable()->after('flight_number');

            // --- PERBAIKAN DI SINI ---
            // Kolom ini juga harus nullable agar bisa ditambahkan ke tabel yang sudah ada isinya.
            $table->string('movement_type')->after('aircraft_type')->nullable(); // Departure atau Arrival

            // Membuat kolom lama menjadi nullable karena tidak akan dipakai lagi
            $table->string('route')->nullable()->change();
            $table->dateTime('arrival_date_time')->nullable()->change();
            $table->dateTime('departure_date_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['airport_id']);
            $table->dropColumn(['airport_id', 'ground_handling', 'flight_number_2', 'movement_type']);
        });
    }
};
