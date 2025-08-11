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
        $table->string('arrival_from')->nullable()->after('route');
        $table->string('departure_from')->nullable()->after('arrival_from');
        $table->dateTime('arrival_date_time')->nullable()->after('actual_time');
        $table->dateTime('departure_date_time')->nullable()->after('arrival_date_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
        });
    }
};
