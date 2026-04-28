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
    public function scanner()
    {
        return view('empleado.scanner');
    }

    /**
     * Escaneo global: identifica automáticamente la habitación por el QR.
     * No requiere asignacion_id — busca la asignación del empleado para hoy.
     */
    public function escanearGlobal(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo_qr' => ['required', 'string'],
        ]);

        $user = $request->user();
        $lenceria = Lenceria::where('codigo_qr', $data['codigo_qr'])->first();

        if (!$lenceria) {
            return response()->json([
                'ok' => false,
                'tipo' => 'no_encontrada',
                'mensaje' => 'Código QR no reconocido.',
            ], 404);
        }

        $habitacion = $lenceria->habitacion;

        if (!$habitacion) {
            return response()->json([
                'ok' => false,
                'tipo' => 'sin_habitacion',
                'mensaje' => 'Esta prenda no tiene habitación asignada.',
            ], 422);
        }

        if ($lenceria->estado === 'extraviada') {
            try {
                broadcast(new AlertaCreada(
                    'extraviada',
                    "Se escaneó prenda EXTRAVIADA {$lenceria->codigo_qr}",
                    $habitacion->id
                ));
            } catch (\Throwable) {}
            return response()->json([
                'ok' => false,
                'tipo' => 'extraviada',
                'mensaje' => 'Esta prenda estaba marcada como extraviada. Reportada al admin.',
                'habitacion' => $habitacion->codigo,
            ], 422);
        }

        // Buscar asignación del empleado para esta habitación hoy
        $asignacion = AsignacionDiaria::where('user_id', $user->id)
            ->where('habitacion_id', $habitacion->id)
            ->whereDate('fecha', today())
            ->first();

        if (!$asignacion) {
            return response()->json([
                'ok' => false,
                'tipo' => 'sin_asignacion',
                'mensaje' => "La prenda pertenece a {$habitacion->codigo}, pero no tienes asignación para esa habitación hoy.",
                'habitacion' => $habitacion->codigo,
            ], 422);
        }

        $item = $asignacion->checklist()
            ->where('lenceria_id', $lenceria->id)
            ->first();

        if (!$item) {
            return response()->json([
                'ok' => false,
                'tipo' => 'no_en_checklist',
                'mensaje' => 'Esta prenda no está en el checklist de ' . $habitacion->codigo . '.',
                'habitacion' => $habitacion->codigo,
            ], 422);
        }

        if ($item->escaneado) {
            $progreso = $asignacion->progreso();
            return response()->json([
                'ok' => true,
                'tipo' => 'ya_escaneada',
                'mensaje' => ucfirst($lenceria->tipo) . ' ya estaba escaneada en ' . $habitacion->codigo . '.',
                'habitacion' => $habitacion->codigo,
                'asignacion_id' => $asignacion->id,
                'progreso' => $progreso,
            ]);
        }

        $item->update(['escaneado' => true, 'escaneado_at' => now()]);

        $progreso = $asignacion->fresh()->progreso();
        $completada = $progreso['hechos'] >= $progreso['total'] && $progreso['total'] > 0;

        try { broadcast(new ChecklistActualizado($asignacion->fresh()))->toOthers(); } catch (\Throwable) {}
        try { broadcast(new HabitacionActualizada($habitacion->fresh()))->toOthers(); } catch (\Throwable) {}

        return response()->json([
            'ok' => true,
            'tipo' => 'ok',
            'mensaje' => ucfirst($lenceria->tipo) . ' escaneada ✓ — ' . $habitacion->codigo,
            'habitacion' => $habitacion->codigo,
            'habitacion_nombre' => $habitacion->nombre,
            'asignacion_id' => $asignacion->id,
            'progreso' => $progreso,
            'completada' => $completada,
        ]);
    }

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
