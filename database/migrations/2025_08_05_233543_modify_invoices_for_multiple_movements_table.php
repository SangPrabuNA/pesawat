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
            // Hapus kolom-kolom lama yang sekarang akan disimpan di tabel invoice_details
            $table->dropColumn([
                'movement_type',
                'actual_time',
                'charge_type',
                'duration_minutes',
                'billed_hours',
                'base_rate',
                'base_charge'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Jika rollback, tambahkan kembali kolom-kolomnya
            $table->string('movement_type')->nullable();
            $table->dateTime('actual_time')->nullable();
            $table->string('charge_type')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->integer('billed_hours')->nullable();
            $table->decimal('base_rate', 15, 4)->nullable();
            $table->decimal('base_charge', 15, 2)->nullable();
        });
    }
};
