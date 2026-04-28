<?php

namespace App\Http\Controllers\Admin;

use App\Events\HabitacionActualizada;
use App\Http\Controllers\Controller;
use App\Models\AsignacionDiaria;
use App\Models\Habitacion;
use App\Models\User;
use App\Services\AsignacionService;
use Illuminate\Http\Request;

class AsignacionController extends Controller
{
    public function create(Request $request)
    {
        $habitaciones = Habitacion::orderBy('codigo')->get();
        $empleados    = User::where('role', 'empleado')->where('activo', true)->orderBy('name')->get();
        $preselect    = $request->query('habitacion');

        return view('admin.asignaciones.create', compact('habitaciones', 'empleados', 'preselect'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'habitacion_id' => 'required|exists:habitaciones,id',
            'user_id'       => 'required|exists:users,id',
            'turno'         => ['required', 'in:mañana,tarde,noche'],
            'fecha'         => 'required|date',
        ]);

        $habitacion = Habitacion::findOrFail($data['habitacion_id']);
        $empleado   = User::findOrFail($data['user_id']);

        // Verificar que no exista ya una asignación para esa habitación ese día
        $existe = AsignacionDiaria::where('habitacion_id', $habitacion->id)
            ->whereDate('fecha', $data['fecha'])
            ->first();

        if ($existe) {
            return back()->withInput()->withErrors([
                'habitacion_id' => "La habitación {$habitacion->codigo} ya tiene una asignación el {$data['fecha']}.",
            ]);
        }

        // Si se asigna para hoy y la habitación está disponible, pasarla a en_limpieza
        if ($data['fecha'] === today()->toDateString() && $habitacion->estado === 'disponible') {
            $habitacion->update(['estado' => 'en_limpieza', 'limpieza_iniciada_at' => now(), 'limpieza_completada_at' => null]);
        }

        $asignacion = AsignacionService::asignarManual(
            $habitacion->fresh(),
            $empleado,
            $data['turno'],
            $data['fecha']
        );

        try { broadcast(new HabitacionActualizada($habitacion->fresh()))->toOthers(); } catch (\Throwable) {}

        return redirect()->route('admin.historial.index')
            ->with('status', "Habitación {$habitacion->codigo} asignada a {$empleado->name}.");
    }

    public function destroy(AsignacionDiaria $asignacion)
    {
        $asignacion->checklist()->delete();
        $asignacion->delete();

        return back()->with('status', 'Asignación eliminada.');
    }
}
