<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comando extends Model
{
    protected $table = 'comandos';

    protected $fillable = [
        'modo',
        'color',
        'umbral_alto',
        'umbral_bajo',
        'enviado',
    ];

    protected $casts = [
        'umbral_alto' => 'float',
        'umbral_bajo' => 'float',
        'enviado' => 'boolean',
    ];
}