<?php

namespace App\Services;

use App\Models\AsignacionDiaria;
use App\Models\ChecklistItem;
use App\Models\Habitacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AsignacionService
{
    /**
     * Determina el turno activo según la hora actual.
     * mañana: 06-14, tarde: 14-22, noche: 22-06
     */
    public static function turnoActual(): string
    {
        $h = (int) now()->format('H');
        if ($h >= 6 && $h < 14) return 'mañana';
        if ($h >= 14 && $h < 22) return 'tarde';
        return 'noche';
    }

    /**
     * Asigna una habitación al empleado del turno activo con menos asignaciones hoy.
     * Crea también los ChecklistItem a partir de las lencerías de la habitación.
     * Idempotente: si ya existe asignación para (habitacion, fecha, turno) la devuelve.
     */
    public static function asignarHabitacion(Habitacion $habitacion, ?string $turno = null): ?AsignacionDiaria
    {
        $turno = $turno ?: self::turnoActual();
        $fecha = today();

        return DB::transaction(function () use ($habitacion, $turno, $fecha) {
            $existente = AsignacionDiaria::where('habitacion_id', $habitacion->id)
                ->whereDate('fecha', $fecha)
                ->where('turno', $turno)
                ->first();

            if ($existente) {
                return $existente;
            }

            // Empleado del turno con menos asignaciones hoy.
            $empleado = User::where('role', 'empleado')
                ->where('activo', true)
                ->where('turno', $turno)
                ->withCount(['asignaciones as asignaciones_hoy' => function ($q) use ($fecha) {
                    $q->whereDate('fecha', $fecha);
                }])
                ->orderBy('asignaciones_hoy', 'asc')
                ->orderBy('id', 'asc')
                ->first();

            if (!$empleado) {
                return null;
            }

            $asignacion = AsignacionDiaria::create([
                'user_id' => $empleado->id,
                'habitacion_id' => $habitacion->id,
                'fecha' => $fecha,
                'turno' => $turno,
                'estado' => 'pendiente',
            ]);

            // Generar checklist con las lencerías que pertenecen a la habitación.
            foreach ($habitacion->lencerias()->get() as $lenceria) {
                ChecklistItem::create([
                    'asignacion_id' => $asignacion->id,
                    'lenceria_id' => $lenceria->id,
                    'escaneado' => false,
                ]);
            }

            return $asignacion;
        });
    }

    /**
     * Asigna manualmente una habitación a un empleado específico.
     * Idempotente: si ya existe asignación para (habitacion, fecha) la devuelve.
     */
    public static function asignarManual(Habitacion $habitacion, User $empleado, string $turno, string $fecha): AsignacionDiaria
    {
        return DB::transaction(function () use ($habitacion, $empleado, $turno, $fecha) {
            $asignacion = AsignacionDiaria::firstOrCreate(
                ['habitacion_id' => $habitacion->id, 'fecha' => $fecha],
                [
                    'user_id' => $empleado->id,
                    'turno'   => $turno,
                    'estado'  => 'pendiente',
                ]
            );

            if ($asignacion->wasRecentlyCreated) {
                foreach ($habitacion->lencerias()->get() as $lenceria) {
                    ChecklistItem::firstOrCreate([
                        'asignacion_id' => $asignacion->id,
                        'lenceria_id'   => $lenceria->id,
                    ], ['escaneado' => false]);
                }
            }

            return $asignacion;
        });
    }
}
