<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDiaria;
use App\Models\Habitacion;
use App\Models\Lenceria;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        // ── Room grid (Alpine.js real-time) ───────────────────────────────────
        $habitaciones = Habitacion::orderBy('codigo')->get()->map(function ($h) {
            $asig = $h->asignacionHoy();
            $progreso = $asig ? $asig->progreso() : ['total' => 0, 'hechos' => 0];
            return [
                'id'              => $h->id,
                'codigo'          => $h->codigo,
                'nombre'          => $h->nombre,
                'estado'          => $h->estado,
                'totalChecklist'  => $progreso['total'],
                'hechosChecklist' => $progreso['hechos'],
                'tieneFaltantes'  => $asig ? (bool) $asig->tiene_faltantes : false,
                'empleado'        => $asig ? optional($asig->empleado)->name : null,
                'asignacionEstado' => $asig?->estado,
            ];
        });

        // ── KPI stats ─────────────────────────────────────────────────────────
        $stats = [
            'total'       => Habitacion::count(),
            'disponibles' => Habitacion::where('estado', 'disponible')->count(),
            'en_limpieza' => Habitacion::where('estado', 'en_limpieza')->count(),
            'ocupadas'    => Habitacion::where('estado', 'ocupada')->count(),
            'completadas' => AsignacionDiaria::whereDate('fecha', today())->where('estado', 'completa')->count(),
            'incompletas' => AsignacionDiaria::whereDate('fecha', today())->where('estado', 'incompleta')->count(),
            'empleados'   => User::where('role', 'empleado')->where('activo', true)->count(),
            'prendas'     => Lenceria::count(),
            'extraviadas' => Lenceria::where('estado', 'extraviada')->count(),
        ];

        // ── Per-employee stats today ───────────────────────────────────────────
        $empleadoStats = User::where('role', 'empleado')
            ->where('activo', true)
            ->orderBy('turno')
            ->get()
            ->map(function ($u) {
                $asigs = AsignacionDiaria::where('user_id', $u->id)
                    ->whereDate('fecha', today())
                    ->get();
                $total      = $asigs->count();
                $completas  = $asigs->where('estado', 'completa')->count();
                $enProceso  = $asigs->where('estado', 'en_proceso')->count();
                $incompletas = $asigs->where('estado', 'incompleta')->count();
                $pct = $total > 0 ? intval($completas / $total * 100) : 0;
                return [
                    'name'        => $u->name,
                    'turno'       => $u->turno,
                    'total'       => $total,
                    'completas'   => $completas,
                    'enProceso'   => $enProceso,
                    'incompletas' => $incompletas,
                    'pendientes'  => max(0, $total - $completas - $enProceso - $incompletas),
                    'pct'         => $pct,
                ];
            })
            ->values();

        return view('admin.dashboard', compact('habitaciones', 'stats', 'empleadoStats'));
    }
}
