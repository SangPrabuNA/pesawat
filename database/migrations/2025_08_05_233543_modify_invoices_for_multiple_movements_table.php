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
            // Define an array of columns to be dropped
            $columnsToDrop = [
                'movement_type',
                'actual_time',
                'charge_type',
                'duration_minutes',
                'billed_hours',
                'base_rate',
                'base_charge'
            ];

            // Loop through and drop each column only if it exists
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // If rollback, add back the columns only if they don't already exist
            if (!Schema::hasColumn('invoices', 'movement_type')) {
                $table->string('movement_type')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'actual_time')) {
                $table->dateTime('actual_time')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'charge_type')) {
                $table->string('charge_type')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'duration_minutes')) {
                $table->integer('duration_minutes')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'billed_hours')) {
                $table->integer('billed_hours')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'base_rate')) {
                $table->decimal('base_rate', 15, 4)->nullable();
            }
            if (!Schema::hasColumn('invoices', 'base_charge')) {
                $table->decimal('base_charge', 15, 2)->nullable();
            }
        });
    }
};
