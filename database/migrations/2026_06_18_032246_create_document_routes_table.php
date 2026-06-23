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
        Schema::create('document_routes', function (Blueprint $table) {
            $table->id('document_route_id');
            $table->foreignId('document_id')->constrained('documents', 'document_id')->onDelete('cascade');
            $table->foreignId('departement_id')->constrained('departements', 'departement_id');
            $table->integer('urutan');
            $table->integer('revisi')->nullable(); //ini isinya urutan berapa yang direvisi
            $table->enum('status', ['none', 'unprocessed', 'onprocess', 'hilang', 'revisi', 'approved'])->default('onprocess');
            $table->dateTime('kapan_onprocess')->nullable();
            $table->dateTime('kapan_approved')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_routes');
    }
};
