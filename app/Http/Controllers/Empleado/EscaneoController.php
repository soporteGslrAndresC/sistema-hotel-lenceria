<?php

namespace App\Http\Controllers\Empleado;

use App\Events\AlertaCreada;
use App\Events\ChecklistActualizado;
use App\Events\HabitacionActualizada;
use App\Http\Controllers\Controller;
use App\Models\AsignacionDiaria;
use App\Models\Lenceria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EscaneoController extends Controller
{
    public function escanear(Request $request, AsignacionDiaria $asignacion): JsonResponse
    {
        abort_unless($asignacion->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'codigo_qr' => ['required', 'string'],
        ]);

        $lenceria = Lenceria::where('codigo_qr', $data['codigo_qr'])->first();

        if (!$lenceria) {
            return response()->json([
                'ok' => false,
                'tipo' => 'no_encontrada',
                'mensaje' => 'Código QR no reconocido.',
            ], 404);
        }

        // Si está marcada como extraviada, alertar.
        if ($lenceria->estado === 'extraviada') {
            broadcast(new AlertaCreada(
                'extraviada',
                "Se escaneó prenda EXTRAVIADA {$lenceria->codigo_qr}",
                $asignacion->habitacion_id
            ));
            return response()->json([
                'ok' => false,
                'tipo' => 'extraviada',
                'mensaje' => 'Esta prenda estaba marcada como extraviada. Reportada al admin.',
            ], 422);
        }

        // Si la prenda no pertenece a esta habitación.
        if ($lenceria->habitacion_id !== $asignacion->habitacion_id) {
            return response()->json([
                'ok' => false,
                'tipo' => 'otra_habitacion',
                'mensaje' => "La prenda pertenece a otra habitación (#{$lenceria->habitacion_id}).",
            ], 422);
        }

        $item = $asignacion->checklist()
            ->where('lenceria_id', $lenceria->id)
            ->first();

        if (!$item) {
            return response()->json([
                'ok' => false,
                'tipo' => 'no_en_checklist',
                'mensaje' => 'Esta prenda no está en el checklist.',
            ], 422);
        }

        if ($item->escaneado) {
            return response()->json([
                'ok'              => true,
                'tipo'            => 'ya_escaneada',
                'mensaje'         => 'Ya estaba escaneada.',
                'checklist_item_id' => $item->id,
                'lenceria_id'     => $lenceria->id,
                'progreso'        => $asignacion->progreso(),
                'completada'      => false,
            ]);
        }

        $item->update(['escaneado' => true, 'escaneado_at' => now()]);

        $progreso = $asignacion->fresh()->progreso();
        $completada = $progreso['hechos'] >= $progreso['total'] && $progreso['total'] > 0;

        try { broadcast(new ChecklistActualizado($asignacion->fresh()))->toOthers(); } catch (\Throwable) {}
        try { broadcast(new HabitacionActualizada($asignacion->habitacion->fresh()))->toOthers(); } catch (\Throwable) {}

        return response()->json([
            'ok'              => true,
            'tipo'            => 'ok',
            'mensaje'         => ucfirst($lenceria->tipo) . ' escaneada ✓',
            'checklist_item_id' => $item->id,
            'lenceria_id'     => $lenceria->id,
            'progreso'        => $progreso,
            'completada'      => $completada,
        ]);
    }
}
