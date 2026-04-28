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
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones_diarias')->cascadeOnDelete();
            $table->foreignId('lenceria_id')->constrained('lencerias')->cascadeOnDelete();
            $table->boolean('escaneado')->default(false);
            $table->timestamp('escaneado_at')->nullable();
            $table->timestamps();
            $table->unique(['asignacion_id', 'lenceria_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};
