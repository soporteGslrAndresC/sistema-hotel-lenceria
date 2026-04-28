<?php

namespace App\Events;

use App\Models\Habitacion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HabitacionActualizada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $habitacionId;
    public string $codigo;
    public string $nombre;
    public string $estado;
    public int $totalChecklist;
    public int $hechosChecklist;
    public bool $tieneFaltantes;
    public ?string $empleado;

    public function __construct(Habitacion $habitacion)
    {
        $asignacion = $habitacion->asignacionHoy();
        $progreso = ['total' => 0, 'hechos' => 0];
        $faltantes = false;
        $empleado = null;

        if ($asignacion) {
            $progreso = $asignacion->progreso();
            $faltantes = (bool) $asignacion->tiene_faltantes;
            $empleado = optional($asignacion->empleado)->name;
        }

        $this->habitacionId = $habitacion->id;
        $this->codigo = $habitacion->codigo;
        $this->nombre = $habitacion->nombre;
        $this->estado = $habitacion->estado;
        $this->totalChecklist = $progreso['total'];
        $this->hechosChecklist = $progreso['hechos'];
        $this->tieneFaltantes = $faltantes;
        $this->empleado = $empleado;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.habitaciones')];
    }

    public function broadcastAs(): string
    {
        return 'habitacion.actualizada';
    }
}
