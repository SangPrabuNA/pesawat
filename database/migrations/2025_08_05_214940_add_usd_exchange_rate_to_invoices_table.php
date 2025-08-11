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
                // Tambahkan kolom untuk kurs dollar setelah kolom currency
                // Dibuat nullable agar data invoice lama tidak error
                $table->decimal('usd_exchange_rate', 15, 2)->nullable()->after('currency');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('usd_exchange_rate');
            });
        }
    };