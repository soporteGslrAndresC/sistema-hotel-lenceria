<?php

namespace Database\Seeders;

use App\Models\AsignacionDiaria;
use App\Models\ChecklistItem;
use App\Models\Habitacion;
use App\Models\Lenceria;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Usuarios ──────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@hotel.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'turno' => null,
                'activo' => true,
            ]
        );

        $empleadosData = [
            ['name' => 'Ana Mañana',  'email' => 'ana@hotel.test',   'turno' => 'mañana'],
            ['name' => 'Bruno Tarde', 'email' => 'bruno@hotel.test', 'turno' => 'tarde'],
            ['name' => 'Carla Noche', 'email' => 'carla@hotel.test', 'turno' => 'noche'],
        ];
        $empleados = [];
        foreach ($empleadosData as $e) {
            $empleados[$e['turno']] = User::updateOrCreate(
                ['email' => $e['email']],
                [
                    'name'     => $e['name'],
                    'password' => Hash::make('password'),
                    'role'     => 'empleado',
                    'turno'    => $e['turno'],
                    'activo'   => true,
                ]
            );
        }

        // ── Habitaciones ──────────────────────────────────────────────────────
        $habitaciones = [];
        for ($i = 101; $i <= 110; $i++) {
            $habitaciones[] = Habitacion::updateOrCreate(
                ['codigo' => "HAB-$i"],
                ['nombre' => "Habitación $i", 'estado' => 'disponible']
            );
        }

        // ── Lencería (3 prendas por habitación) ───────────────────────────────
        $tipos = ['sabana', 'funda', 'toalla', 'almohada', 'cobija', 'bata'];
        foreach ($habitaciones as $idx => $hab) {
            if ($hab->lencerias()->count() >= 3) continue;
            for ($k = 0; $k < 3; $k++) {
                Lenceria::create([
                    'codigo_qr'     => 'LEN-' . strtoupper(Str::random(10)),
                    'tipo'          => $tipos[($idx + $k) % count($tipos)],
                    'estado'        => 'en_habitacion',
                    'habitacion_id' => $hab->id,
                ]);
            }
        }

        // ── Asignaciones del día de hoy ────────────────────────────────────────
        // Limpiar las del día para evitar duplicados al re-correr el seeder.
        $hoy = today()->toDateString();
        $asignacionIds = AsignacionDiaria::whereDate('fecha', $hoy)->pluck('id');
        ChecklistItem::whereIn('asignacion_id', $asignacionIds)->delete();
        AsignacionDiaria::whereDate('fecha', $hoy)->delete();

        // Distribuir: Ana → HAB-101..104 (mañana)
        //             Bruno → HAB-105..107 (tarde)
        //             Carla → HAB-108..110 (noche)
        $distribucion = [
            'mañana' => array_slice($habitaciones, 0, 4),
            'tarde'  => array_slice($habitaciones, 4, 3),
            'noche'  => array_slice($habitaciones, 7, 3),
        ];

        $estadosDemoTurno = [
            'mañana' => ['completa', 'en_proceso', 'pendiente', 'pendiente'],
            'tarde'  => ['en_proceso', 'pendiente', 'pendiente'],
            'noche'  => ['pendiente', 'pendiente', 'pendiente'],
        ];

        foreach ($distribucion as $turno => $habs) {
            $empleado = $empleados[$turno];
            foreach ($habs as $pos => $hab) {
                $estadoDemo = $estadosDemoTurno[$turno][$pos] ?? 'pendiente';
                $hab->update(['estado' => $estadoDemo === 'completa' ? 'disponible' : 'en_limpieza']);

                $asignacion = AsignacionDiaria::create([
                    'user_id'       => $empleado->id,
                    'habitacion_id' => $hab->id,
                    'fecha'         => $hoy,
                    'turno'         => $turno,
                    'estado'        => $estadoDemo,
                    'iniciada_at'   => in_array($estadoDemo, ['en_proceso', 'completa']) ? now()->subMinutes(rand(10, 60)) : null,
                    'completada_at' => $estadoDemo === 'completa' ? now()->subMinutes(rand(1, 9)) : null,
                    'tiene_faltantes' => false,
                ]);

                // Generar checklist desde las lencerías de la habitación.
                foreach ($hab->lencerias as $lenceria) {
                    $escaneado = $estadoDemo === 'completa';
                    ChecklistItem::create([
                        'asignacion_id' => $asignacion->id,
                        'lenceria_id'   => $lenceria->id,
                        'escaneado'     => $escaneado,
                        'escaneado_at'  => $escaneado ? now()->subMinutes(rand(1, 30)) : null,
                    ]);
                }
            }
        }
    }
}
