    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::table('invoices', function (Blueprint $table) {
                // Mengubah kolom agar bisa null dan memiliki default value
                $table->decimal('usd_exchange_rate', 15, 2)->nullable()->default(0)->change();
            });
        }

        public function down(): void
        {
            Schema::table('invoices', function (Blueprint $table) {
                // Kembalikan ke state semula jika di-rollback
                $table->decimal('usd_exchange_rate', 15, 2)->nullable()->default(null)->change();
            });
        }
    };
