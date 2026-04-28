<?php

namespace App\Http\Controllers\Admin;

use App\Events\HabitacionActualizada;
use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use App\Services\AsignacionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HabitacionController extends Controller
{
    public function index()
    {
        $habitaciones = Habitacion::withCount('lencerias')->orderBy('codigo')->get();
        return view('admin.habitaciones.index', compact('habitaciones'));
    }

    public function create()
    {
        return view('admin.habitaciones.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'unique:habitaciones,codigo'],
            'nombre' => ['required', 'string', 'max:255'],
            'estado' => ['required', Rule::in(['disponible', 'ocupada', 'en_limpieza', 'fuera_servicio'])],
        ]);

        $habitacion = Habitacion::create($data);

        if ($habitacion->estado === 'en_limpieza') {
            $habitacion->update(['limpieza_iniciada_at' => now()]);
            AsignacionService::asignarHabitacion($habitacion);
        }

        try { broadcast(new HabitacionActualizada($habitacion->fresh()))->toOthers(); } catch (\Throwable) {}

        return redirect()->route('admin.habitaciones.index')->with('status', 'Habitación creada');
    }

    public function edit(Habitacion $habitacion)
    {
        return view('admin.habitaciones.edit', compact('habitacion'));
    }

    public function update(Request $request, Habitacion $habitacion)
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', Rule::unique('habitaciones', 'codigo')->ignore($habitacion->id)],
            'nombre' => ['required', 'string', 'max:255'],
            'estado' => ['required', Rule::in(['disponible', 'ocupada', 'en_limpieza', 'fuera_servicio'])],
        ]);

        $estadoAnterior = $habitacion->estado;
        $habitacion->update($data);

        // Si pasó a en_limpieza, asignar automáticamente
        if ($estadoAnterior !== 'en_limpieza' && $data['estado'] === 'en_limpieza') {
            $habitacion->update(['limpieza_iniciada_at' => now(), 'limpieza_completada_at' => null]);
            AsignacionService::asignarHabitacion($habitacion->fresh());
        }

        try { broadcast(new HabitacionActualizada($habitacion->fresh()))->toOthers(); } catch (\Throwable) {}

        return redirect()->route('admin.habitaciones.index')->with('status', 'Habitación actualizada');
    }

    public function destroy(Habitacion $habitacion)
    {
        $habitacion->delete();
        return redirect()->route('admin.habitaciones.index')->with('status', 'Habitación eliminada');
    }

    public function cambiarEstado(Request $request, Habitacion $habitacion)
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(['disponible', 'ocupada', 'en_limpieza', 'fuera_servicio'])],
        ]);

        $estadoAnterior = $habitacion->estado;
        $habitacion->update(['estado' => $data['estado']]);

        if ($estadoAnterior !== 'en_limpieza' && $data['estado'] === 'en_limpieza') {
            $habitacion->update(['limpieza_iniciada_at' => now(), 'limpieza_completada_at' => null]);
            AsignacionService::asignarHabitacion($habitacion->fresh());
        }

        try { broadcast(new HabitacionActualizada($habitacion->fresh()))->toOthers(); } catch (\Throwable) {}

        return back()->with('status', 'Estado actualizado');
    }
}
