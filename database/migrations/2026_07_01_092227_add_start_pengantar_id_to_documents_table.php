<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('start_pengantar_id')
                ->nullable()
                ->after('pengantar_id') // opsional, biar posisi kolomnya rapi
                ->constrained('users', 'user_id');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['start_pengantar_id']);
            $table->dropColumn('start_pengantar_id');
        });
    }
};
