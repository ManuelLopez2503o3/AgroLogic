<?php

namespace App\Http\Controllers;

use App\Models\IncidenteFuego;
use Illuminate\Http\JsonResponse;

class IncidenteFuegoController extends Controller
{
    /**
     * Último incidente de fuego registrado
     * (activo o ya cerrado con su reporte).
     */
    public function ultimo(): JsonResponse
    {
        $incidente = IncidenteFuego::query()
            ->latest('fecha_inicio')
            ->first();

        if (!$incidente) {
            return response()->json([
                'hay_incidente' => false,
            ]);
        }

        return response()->json([
            'hay_incidente' => true,
            'id' => $incidente->id,
            'activo' => $incidente->activo,
            'fecha_inicio' => optional($incidente->fecha_inicio)->format('Y-m-d H:i:s'),
            'fecha_fin' => optional($incidente->fecha_fin)->format('Y-m-d H:i:s'),
            'pico_temperatura' => $incidente->pico_temperatura,
            'humedad_final' => $incidente->humedad_final,
            'duracion_segundos' => $incidente->duracion_segundos,
            'reporte_ia' => $incidente->reporte_ia,
        ]);
    }
}