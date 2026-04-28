<?php

use App\Http\Controllers\Admin\AsignacionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmpleadoController;
use App\Http\Controllers\Admin\HabitacionController as AdminHabitacionController;
use App\Http\Controllers\Admin\HistorialController;
use App\Http\Controllers\Admin\LenceriaController;
use App\Http\Controllers\Admin\QrController;
use App\Http\Controllers\Empleado\DashboardController as EmpleadoDashboardController;
use App\Http\Controllers\Empleado\EscaneoController;
use App\Http\Controllers\Empleado\HabitacionController as EmpleadoHabitacionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('empleado.dashboard');
    }
    return redirect()->route('login');
});

// Redirige /dashboard al panel apropiado (Breeze por defecto)
Route::get('/dashboard', function () {
    return Auth::user()?->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('empleado.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Empleado
Route::middleware(['auth', 'role:empleado'])->prefix('empleado')->name('empleado.')->group(function () {
    Route::get('/', EmpleadoDashboardController::class)->name('dashboard');
    Route::get('/asignacion/{asignacion}', [EmpleadoHabitacionController::class, 'show'])->name('asignacion.show');
    Route::post('/asignacion/{asignacion}/escanear', [EscaneoController::class, 'escanear'])->name('asignacion.escanear');
    Route::post('/asignacion/{asignacion}/completar', [EmpleadoHabitacionController::class, 'completar'])->name('asignacion.completar');
});

// Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::resource('empleados', EmpleadoController::class)->parameters(['empleados' => 'empleado'])->except(['show']);

    Route::resource('habitaciones', AdminHabitacionController::class)->parameters(['habitaciones' => 'habitacion'])->except(['show']);
    Route::patch('habitaciones/{habitacion}/estado', [AdminHabitacionController::class, 'cambiarEstado'])->name('habitaciones.estado');

    Route::resource('lencerias', LenceriaController::class)->parameters(['lencerias' => 'lenceria'])->except(['show']);
    Route::get('lencerias-imprimir', [QrController::class, 'imprimir'])->name('lencerias.imprimir');
    Route::get('lencerias/{lenceria}/qr.svg', [QrController::class, 'svg'])->name('lencerias.qr');

    Route::get('asignaciones/crear', [AsignacionController::class, 'create'])->name('asignaciones.create');
    Route::post('asignaciones', [AsignacionController::class, 'store'])->name('asignaciones.store');
    Route::delete('asignaciones/{asignacion}', [AsignacionController::class, 'destroy'])->name('asignaciones.destroy');

    Route::get('historial', [HistorialController::class, 'index'])->name('historial.index');
    Route::get('historial/{asignacion}', [HistorialController::class, 'show'])->name('historial.show');
});

require __DIR__.'/auth.php';
