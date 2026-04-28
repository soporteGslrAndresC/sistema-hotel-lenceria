<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsignacionDiaria extends Model
{
    use HasFactory;

    protected $table = 'asignaciones_diarias';

    protected $fillable = [
        'user_id', 'habitacion_id', 'fecha', 'turno',
        'estado', 'iniciada_at', 'completada_at', 'tiene_faltantes',
    ];

    protected $casts = [
        'fecha' => 'date',
        'iniciada_at' => 'datetime',
        'completada_at' => 'datetime',
        'tiene_faltantes' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function checklist(): HasMany
    {
        return $this->hasMany(ChecklistItem::class, 'asignacion_id');
    }

    public function progreso(): array
    {
        $total = $this->checklist()->count();
        $hechos = $this->checklist()->where('escaneado', true)->count();
        return ['total' => $total, 'hechos' => $hechos];
    }
}
