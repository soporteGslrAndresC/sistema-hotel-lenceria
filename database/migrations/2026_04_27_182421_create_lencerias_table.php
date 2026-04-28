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
        Schema::create('lencerias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_qr')->unique();
            $table->enum('tipo', ['sabana', 'funda', 'toalla', 'almohada', 'cobija', 'bata']);
            $table->enum('estado', ['en_habitacion', 'en_lavanderia', 'extraviada'])->default('en_habitacion');
            $table->foreignId('habitacion_id')->constrained('habitaciones')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lencerias');
    }
};
