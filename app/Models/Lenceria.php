<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lenceria extends Model
{
    use HasFactory;

    protected $table = 'lencerias';

    protected $fillable = ['codigo_qr', 'tipo', 'estado', 'habitacion_id'];

    public const TIPOS = ['sabana', 'funda', 'toalla', 'almohada', 'cobija', 'bata'];
    public const ESTADOS = ['en_habitacion', 'en_lavanderia', 'extraviada'];

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }
}
