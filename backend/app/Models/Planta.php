<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planta extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'humedad_min',
        'humedad_max',
        'temperatura_min',
        'temperatura_max',
        'nutrientes_recomendados',
        'descripcion',
    ];
}