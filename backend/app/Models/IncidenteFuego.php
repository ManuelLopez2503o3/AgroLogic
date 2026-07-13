<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidenteFuego extends Model
{
    protected $table = 'incidentes_fuego';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'pico_temperatura',
        'humedad_final',
        'duracion_segundos',
        'reporte_ia',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'pico_temperatura' => 'float',
        'humedad_final' => 'float',
        'activo' => 'boolean',
    ];
}