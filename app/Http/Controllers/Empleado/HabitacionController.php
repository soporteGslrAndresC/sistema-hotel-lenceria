<?php

namespace App\Http\Controllers\Empleado;

use App\Events\HabitacionActualizada;
use App\Events\AlertaCreada;
use App\Http\Controllers\Controller;
use App\Models\AsignacionDiaria;
use Illuminate\Http\Request;

class HabitacionController extends Controller
{
    public function show(Request $request, AsignacionDiaria $asignacion)
    {
        abort_unless($asignacion->user_id === $request->user()->id, 403);

        $asignacion->load(['habitacion', 'checklist.lenceria']);

        // Marcar como en_proceso si todavía está pendiente.
        if ($asignacion->estado === 'pendiente') {
            $asignacion->update([
                'estado' => 'en_proceso',
                'iniciada_at' => now(),
            ]);
            $asignacion->refresh();
        }

        // Pre-computar checkItems para Alpine (evita PHP complejo en @json dentro de <script>).
        $checkItemsData = $asignacion->checklist->mapWithKeys(function ($i) {
            return [$i->id => [
                'escaneado'  => (bool) $i->escaneado,
                'lenceria_id' => $i->lenceria_id,
                'tipo'       => $i->lenceria?->tipo ?? '',
                'codigo_qr'  => $i->lenceria?->codigo_qr ?? '',
            ]];
        })->toArray();

        // Conteos por tipo de prenda.
        $conteos = [];
        foreach ($asignacion->checklist as $item) {
            $tipo = $item->lenceria?->tipo ?? 'desconocido';
            $conteos[$tipo] ??= ['hechos' => 0, 'total' => 0];
            $conteos[$tipo]['total']++;
            if ($item->escaneado) {
                $conteos[$tipo]['hechos']++;
            }
        }

        return view('empleado.habitacion', compact('asignacion', 'conteos', 'checkItemsData'));
    }

    public function completar(Request $request, AsignacionDiaria $asignacion)
    {
        abort_unless($asignacion->user_id === $request->user()->id, 403);

        $progreso = $asignacion->progreso();
        $faltantes = $progreso['hechos'] < $progreso['total'];

        $asignacion->update([
            'estado' => $faltantes ? 'incompleta' : 'completa',
            'completada_at' => now(),
            'tiene_faltantes' => $faltantes,
        ]);

        $habitacion = $asignacion->habitacion;
        if (!$faltantes) {
            $habitacion->update([
                'estado' => 'disponible',
                'limpieza_completada_at' => now(),
            ]);
        }

        try { broadcast(new HabitacionActualizada($habitacion->fresh()))->toOthers(); } catch (\Throwable) {}

        if ($faltantes) {
            try {
                broadcast(new AlertaCreada(
                    'faltantes',
                    "Habitación {$habitacion->codigo} cerrada con {$progreso['hechos']}/{$progreso['total']} prendas",
                    $habitacion->id
                ));
            } catch (\Throwable) {}
        }

        return redirect()->route('empleado.dashboard')->with('status',
            $faltantes ? 'Cerrada con faltantes' : 'Habitación completa');
    }
}
