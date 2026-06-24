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
        Schema::create('section_heads', function (Blueprint $table) {
            $table->id('section_head_id');
            $table->string('nama_section_head');
            $table->string('nama_pjs')->nullable();
            $table->date('tanggal_mulai_pjs')->nullable();
            $table->date('tanggal_akhir_pjs')->nullable();
            $table->foreignId('departement_id')->constrained('departements', 'departement_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_heads');
    }
};
