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
        Schema::create('asignaciones_diarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('turno');
            $table->enum('estado', ['pendiente', 'en_proceso', 'completa', 'incompleta'])->default('pendiente');
            $table->timestamp('iniciada_at')->nullable();
            $table->timestamp('completada_at')->nullable();
            $table->boolean('tiene_faltantes')->default(false);
            $table->timestamps();
            $table->unique(['habitacion_id', 'fecha', 'turno']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_diarias');
    }
};
