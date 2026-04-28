<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDiaria;
use App\Models\Habitacion;
use App\Models\User;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        $query = AsignacionDiaria::with(['habitacion', 'empleado'])
            ->orderByDesc('fecha')->orderByDesc('id');

        if ($habId = $request->get('habitacion_id')) {
            $query->where('habitacion_id', $habId);
        }
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($fecha = $request->get('fecha')) {
            $query->whereDate('fecha', $fecha);
        }

        $asignaciones = $query->paginate(25)->withQueryString();
        $habitaciones = Habitacion::orderBy('codigo')->get();
        $empleados = User::where('role', 'empleado')->orderBy('name')->get();

        return view('admin.historial.index', compact('asignaciones', 'habitaciones', 'empleados'));
    }

    public function show(AsignacionDiaria $asignacion)
    {
        $asignacion->load(['habitacion', 'empleado', 'checklist.lenceria']);
        return view('admin.historial.show', compact('asignacion'));
    }
}
