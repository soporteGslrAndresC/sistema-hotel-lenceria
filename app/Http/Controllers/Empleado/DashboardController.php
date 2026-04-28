<?php

namespace App\Http\Controllers\Empleado;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $asignaciones = $user->asignaciones()
            ->with(['habitacion'])
            ->whereDate('fecha', today())
            ->orderBy('estado')
            ->get();

        return view('empleado.dashboard', compact('asignaciones'));
    }
}
