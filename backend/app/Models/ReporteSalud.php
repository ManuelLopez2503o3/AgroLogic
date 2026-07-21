<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteSalud extends Model
{
    protected $table = 'reportes_salud';

    protected $fillable = [
        'fecha',
        'temperatura_promedio',
        'temperatura_min',
        'temperatura_max',
        'humedad_promedio',
        'diagnostico_ia',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
    ];
}