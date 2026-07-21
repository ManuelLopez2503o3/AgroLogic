<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DecisionIA extends Model
{
    protected $table = 'decisiones_ia';

    protected $fillable = [
        'fecha_hora',
        'nivel_riesgo',
        'temperatura',
        'humedad',
        'accion_ejecutada',
        'motivo',
        'ejecutada_automaticamente',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'ejecutada_automaticamente' => 'boolean',
    ];
}