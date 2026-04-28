<?php

namespace App\Events;

use App\Models\AsignacionDiaria;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChecklistActualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $asignacionId;
    public int $habitacionId;
    public int $total;
    public int $hechos;

    public function __construct(AsignacionDiaria $asignacion)
    {
        $progreso = $asignacion->progreso();
        $this->asignacionId = $asignacion->id;
        $this->habitacionId = $asignacion->habitacion_id;
        $this->total = $progreso['total'];
        $this->hechos = $progreso['hechos'];
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.habitaciones')];
    }

    public function broadcastAs(): string
    {
        return 'checklist.actualizado';
    }
}
