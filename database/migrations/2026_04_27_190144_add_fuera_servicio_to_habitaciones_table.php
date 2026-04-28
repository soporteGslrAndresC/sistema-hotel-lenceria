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
        $driver = \DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            \DB::statement("ALTER TABLE habitaciones MODIFY COLUMN estado ENUM('disponible','ocupada','en_limpieza','fuera_servicio') NOT NULL DEFAULT 'disponible'");
        }
        // SQLite: CHECK constraints are too restrictive to alter; validation handled in app layer.
        // For a fresh install the original migration can be edited to include 'fuera_servicio'.
    }

    public function down(): void
    {
        $driver = \DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            \DB::statement("ALTER TABLE habitaciones MODIFY COLUMN estado ENUM('disponible','ocupada','en_limpieza') NOT NULL DEFAULT 'disponible'");
        }
    }
};
