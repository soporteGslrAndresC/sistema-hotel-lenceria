<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RevisarHabitacionesLentas extends Command
{
    protected $signature = 'app:revisar-habitaciones-lentas';

    protected $description = 'Detecta habitaciones en limpieza más de 2 horas y dispara alertas';

    public function handle()
    {
        $umbral = now()->subHours(2);

        $habitaciones = \App\Models\Habitacion::where('estado', 'en_limpieza')
            ->whereNotNull('limpieza_iniciada_at')
            ->where('limpieza_iniciada_at', '<', $umbral)
            ->get();

        foreach ($habitaciones as $h) {
            $minutos = (int) $h->limpieza_iniciada_at->diffInMinutes(now());
            $msg = "Habitación {$h->codigo} lleva {$minutos} min en limpieza sin completarse";
            $this->warn($msg);
            try { broadcast(new \App\Events\AlertaCreada('lenta', $msg, $h->id)); } catch (\Throwable) {}
        }

        $this->info('Revisión completada: ' . $habitaciones->count() . ' alerta(s)');
        return self::SUCCESS;
    }
}
