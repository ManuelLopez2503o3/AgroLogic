<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHistorial extends Model
{
    use HasFactory;

    protected $table = 'chat_historial';

    protected $fillable = [
        'user_id',
        'planta_id',
        'pregunta',
        'respuesta',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function planta()
    {
        return $this->belongsTo(Planta::class);
    }
}