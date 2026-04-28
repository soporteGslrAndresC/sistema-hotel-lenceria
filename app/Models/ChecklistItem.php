<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = ['asignacion_id', 'lenceria_id', 'escaneado', 'escaneado_at'];

    protected $casts = [
        'escaneado' => 'boolean',
        'escaneado_at' => 'datetime',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionDiaria::class, 'asignacion_id');
    }

    public function lenceria(): BelongsTo
    {
        return $this->belongsTo(Lenceria::class);
    }
}
