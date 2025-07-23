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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('airline');
            $table->string('flight_number');
            $table->string('registration');
            $table->string('aircraft_type');
            $table->string('route');
            $table->enum('service_type', ['APP', 'TWR', 'AFIS']);
            $table->enum('flight_type', ['Domestik', 'Internasional']);
            $table->enum('charge_type', ['Advance', 'Extend']);
            $table->time('operational_hour_start');
            $table->time('operational_hour_end');
            $table->dateTime('actual_time'); // Waktu ATA atau ATD
            $table->integer('duration_minutes'); // Durasi advance/extend dalam menit
            $table->integer('billed_hours');     // Jam yang ditagihkan (sudah dibulatkan)
            $table->decimal('base_rate', 15, 2); // Tarif per jam
            $table->decimal('base_charge', 15, 2); // Total biaya dasar (tarif x jam)
            $table->decimal('ppn_charge', 15, 2);  // Biaya PPN 11%
            $table->decimal('total_charge', 15, 2); // Total tagihan
            $table->string('currency', 3); // IDR atau USD
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
