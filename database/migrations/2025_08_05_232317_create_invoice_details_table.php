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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('movement_type'); // Isinya akan 'Arrival' atau 'Departure'
            $table->dateTime('actual_time');
            $table->string('charge_type'); // Isinya akan 'Advance' atau 'Extend'
            $table->integer('duration_minutes');
            $table->integer('billed_hours');
            $table->decimal('base_rate', 15, 4); // Tambah presisi untuk nilai dolar
            $table->decimal('base_charge', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
