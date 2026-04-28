<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertaCreada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $tipo; // 'faltantes' | 'lenta' | 'extraviada'
    public string $mensaje;
    public ?int $habitacionId;

    public function __construct(string $tipo, string $mensaje, ?int $habitacionId = null)
    {
        $this->tipo = $tipo;
        $this->mensaje = $mensaje;
        $this->habitacionId = $habitacionId;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.alertas')];
    }

    public function broadcastAs(): string
    {
        return 'alerta';
    }
}
