<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    /**
     * Obtener el valor actual de una configuracion.
     * Ej: GET /api/configuraciones/temperatura_maxima
     */
    public function show(string $clave): JsonResponse
    {
        $config = Configuracion::where('clave', $clave)->first();

        return response()->json([
            'clave' => $clave,
            'valor' => $config?->valor ?? 35, // valor por defecto si no existe aun
        ]);
    }

    /**
     * CA-01/CA-02: validar y sobrescribir el valor
     * (solo administradores).
     */
    public function update(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'administrador') {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        $datos = $request->validate([
            'clave' => ['required', 'string'],
            'valor' => ['required', 'numeric', 'between:15,50'],
        ]);

        $config = Configuracion::updateOrCreate(
            ['clave' => $datos['clave']],
            ['valor' => $datos['valor']]
        );

        return response()->json([
            'message' => 'Configuración actualizada',
            'clave' => $config->clave,
            'valor' => (float) $config->valor,
        ]);
    }
}