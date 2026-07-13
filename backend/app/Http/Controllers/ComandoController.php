<?php

namespace App\Http\Controllers;

use App\Models\Comando;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComandoController extends Controller
{
    /**
     * React manda un comando nuevo desde el dashboard.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'modo' => ['required', 'in:manual,auto'],
            'color' => ['nullable', 'in:rojo,azul,amarillo,verde,apagado'],
            'umbral_alto' => ['nullable', 'numeric'],
            'umbral_bajo' => ['nullable', 'numeric'],
        ]);

        $comando = Comando::create([
            'modo' => $datos['modo'],
            'color' => $datos['color'] ?? null,
            'umbral_alto' => $datos['umbral_alto'] ?? null,
            'umbral_bajo' => $datos['umbral_bajo'] ?? null,
            'enviado' => false,
        ]);

        return response()->json($comando, 201);
    }

    /**
     * serial_bridge.py pregunta si hay algo pendiente de mandar al ESP32.
     */
    public function pendiente(): JsonResponse
    {
        $comando = Comando::where('enviado', false)
            ->latest()
            ->first();

        if (!$comando) {
            return response()->json(['hay_comando' => false]);
        }

        $comando->update(['enviado' => true]);

        return response()->json([
            'hay_comando' => true,
            'modo' => $comando->modo,
            'color' => $comando->color,
            'umbral_alto' => $comando->umbral_alto,
            'umbral_bajo' => $comando->umbral_bajo,
        ]);
    }
}