<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habitacion extends Model
{
    use HasFactory;

    protected $table = 'habitaciones';

    protected $fillable = [
        'codigo', 'nombre', 'estado',
        'limpieza_iniciada_at', 'limpieza_completada_at',
    ];

    protected $casts = [
        'limpieza_iniciada_at' => 'datetime',
        'limpieza_completada_at' => 'datetime',
    ];

    public function lencerias(): HasMany
    {
        return $this->hasMany(Lenceria::class);
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionDiaria::class);
    }

    public function asignacionHoy(?string $turno = null)
    {
        return $this->asignaciones()
            ->whereDate('fecha', today())
            ->when($turno, fn($q) => $q->where('turno', $turno))
            ->latest('id')
            ->first();
    }
}
