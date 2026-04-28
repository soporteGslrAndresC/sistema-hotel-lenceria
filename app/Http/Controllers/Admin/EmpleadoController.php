<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmpleadoController extends Controller
{
    public function index()
    {
        $empleados = User::where('role', 'empleado')->orderBy('name')->get();
        return view('admin.empleados.index', compact('empleados'));
    }

    public function create()
    {
        return view('admin.empleados.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'turno' => ['required', Rule::in(['mañana', 'tarde', 'noche'])],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'turno' => $data['turno'],
            'role' => 'empleado',
            'activo' => true,
        ]);

        return redirect()->route('admin.empleados.index')->with('status', 'Empleado creado');
    }

    public function edit(User $empleado)
    {
        abort_unless($empleado->role === 'empleado', 404);
        return view('admin.empleados.edit', compact('empleado'));
    }

    public function update(Request $request, User $empleado)
    {
        abort_unless($empleado->role === 'empleado', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($empleado->id)],
            'turno' => ['required', Rule::in(['mañana', 'tarde', 'noche'])],
            'activo' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $empleado->name = $data['name'];
        $empleado->email = $data['email'];
        $empleado->turno = $data['turno'];
        $empleado->activo = (bool) ($data['activo'] ?? false);
        if (!empty($data['password'])) {
            $empleado->password = Hash::make($data['password']);
        }
        $empleado->save();

        return redirect()->route('admin.empleados.index')->with('status', 'Empleado actualizado');
    }

    public function destroy(User $empleado)
    {
        abort_unless($empleado->role === 'empleado', 404);
        $empleado->delete();
        return redirect()->route('admin.empleados.index')->with('status', 'Empleado eliminado');
    }
}
