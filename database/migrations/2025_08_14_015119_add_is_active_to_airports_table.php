<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('airports', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('op_end');
        });
    }
    public function down(): void {
        Schema::table('airports', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
