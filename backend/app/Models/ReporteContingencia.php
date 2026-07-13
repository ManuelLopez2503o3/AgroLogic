<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteContingencia extends Model
{
    protected $table = 'reportes_contingencia';

    // La tabla no tiene created_at/updated_at
    public $timestamps = false;

    protected $fillable = [
        'tipo_evento',
        'temperatura_pico',
        'duracion_segundos',
        'diagnostico_ia',
        'recomendacion_ia',
        'fecha_siniestro',
    ];

    protected $casts = [
        'temperatura_pico' => 'float',
        'fecha_siniestro' => 'datetime',
    ];
}