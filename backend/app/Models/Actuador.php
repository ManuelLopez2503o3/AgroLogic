<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actuador extends Model
{
    protected $table = 'actuadores';

    protected $fillable = [
        'device_name',
        'mode',
        'bomba_status',
        'luces_status',
        'updated_by',
    ];

    protected $casts = [
        'bomba_status' => 'boolean',
        'luces_status' => 'boolean',
    ];
}