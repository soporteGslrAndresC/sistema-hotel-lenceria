<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use App\Models\Lenceria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LenceriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Lenceria::with('habitacion')->orderBy('id', 'desc');

        if ($habId = $request->get('habitacion_id')) {
            $query->where('habitacion_id', $habId);
        }
        if ($tipo = $request->get('tipo')) {
            $query->where('tipo', $tipo);
        }
        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        $lencerias = $query->paginate(20)->withQueryString();
        $habitaciones = Habitacion::orderBy('codigo')->get();

        return view('admin.lencerias.index', compact('lencerias', 'habitaciones'));
    }

    public function create()
    {
        $habitaciones = Habitacion::orderBy('codigo')->get();
        return view('admin.lencerias.create', compact('habitaciones'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo' => ['required', Rule::in(Lenceria::TIPOS)],
            'estado' => ['required', Rule::in(Lenceria::ESTADOS)],
            'habitacion_id' => ['required', 'exists:habitaciones,id'],
            'cantidad' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $cantidad = $data['cantidad'] ?? 1;
        for ($i = 0; $i < $cantidad; $i++) {
            Lenceria::create([
                'codigo_qr' => 'LEN-' . strtoupper(Str::random(10)),
                'tipo' => $data['tipo'],
                'estado' => $data['estado'],
                'habitacion_id' => $data['habitacion_id'],
            ]);
        }

        return redirect()->route('admin.lencerias.index')
            ->with('status', "$cantidad prenda(s) creada(s)");
    }

    public function edit(Lenceria $lenceria)
    {
        $habitaciones = Habitacion::orderBy('codigo')->get();
        return view('admin.lencerias.edit', compact('lenceria', 'habitaciones'));
    }

    public function update(Request $request, Lenceria $lenceria)
    {
        $data = $request->validate([
            'tipo' => ['required', Rule::in(Lenceria::TIPOS)],
            'estado' => ['required', Rule::in(Lenceria::ESTADOS)],
            'habitacion_id' => ['required', 'exists:habitaciones,id'],
        ]);

        $lenceria->update($data);
        return redirect()->route('admin.lencerias.index')->with('status', 'Prenda actualizada');
    }

    public function destroy(Lenceria $lenceria)
    {
        $lenceria->delete();
        return redirect()->route('admin.lencerias.index')->with('status', 'Prenda eliminada');
    }
}
