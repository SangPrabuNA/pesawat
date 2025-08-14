<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            // Kolom ini boleh null karena Master dan User biasa tidak terikat pada satu bandara
            $table->foreignId('airport_id')->nullable()->after('role')->constrained()->onDelete('set null');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['airport_id']);
            $table->dropColumn('airport_id');
        });
    }
};
