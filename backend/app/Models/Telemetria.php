<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telemetria extends Model
{
    protected $table = 'telemetrias';

    public $timestamps = false;

    protected $fillable = [
        'temperatura',
        'humedad',
        'estado',
        'fecha_registro',
    ];

    protected $casts = [
        'temperatura' => 'float',
        'humedad' => 'float',
        'fecha_registro' => 'datetime',
    ];
}