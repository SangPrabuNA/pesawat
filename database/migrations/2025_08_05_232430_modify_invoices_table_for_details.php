<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('invoices', function (Blueprint $table) {
            // Tambahkan kolom baru untuk waktu arrival dan departure
            $table->dateTime('arrival_time')->nullable()->after('aircraft_type');
            $table->dateTime('departure_time')->nullable()->after('arrival_time');

            // Hapus kolom lama yang sekarang akan ada di tabel invoice_details
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
    public function down(): void {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['arrival_time', 'departure_time']);
            // Tambahkan kembali kolom lama jika rollback
            $table->string('movement_type');
            $table->dateTime('actual_time');
            $table->string('charge_type');
            $table->integer('duration_minutes');
            $table->integer('billed_hours');
            $table->decimal('base_rate', 15, 2);
            $table->decimal('base_charge', 15, 2);
        });
    }
};