<?php

namespace App\Http\Controllers;

use App\Models\Actuador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActuadorController extends Controller
{
    /**
     * Estado actual de los actuadores.
     * Pública: la consulta serial_bridge.py cíclicamente.
     */
    public function status(): JsonResponse
    {
        $actuador = Actuador::first();

        if (!$actuador) {
            return response()->json([
                'bomba_status' => false,
                'luces_status' => false,
            ]);
        }

        return response()->json([
            'bomba_status' => $actuador->bomba_status,
            'luces_status' => $actuador->luces_status,
        ]);
    }

    /**
     * CA-02: cambiar el estado (solo administradores).
     */
    public function update(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'administrador') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $datos = $request->validate([
            'bomba_status' => ['sometimes', 'boolean'],
            'luces_status' => ['sometimes', 'boolean'],
        ]);

        $actuador = Actuador::first();
        $actuador->update($datos);

        return response()->json([
            'message' => 'Actuador actualizado',
            'bomba_status' => $actuador->bomba_status,
            'luces_status' => $actuador->luces_status,
        ]);
    }
}