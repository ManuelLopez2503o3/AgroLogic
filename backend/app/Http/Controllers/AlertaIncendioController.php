<?php

namespace App\Http\Controllers;

use App\Models\ReporteContingencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertaIncendioController extends Controller
{
    /**
     * CA-03: recibe el aviso del puente Python cuando el
     * ESP32 activa o desactiva la bomba de emergencia.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'alerta_incendio' => ['required', 'boolean'],
            'temperatura' => ['required', 'numeric'],
        ]);

        if ($datos['alerta_incendio']) {
            $abierto = ReporteContingencia::where('tipo_evento', 'incendio')
                ->whereNull('duracion_segundos')
                ->latest('fecha_siniestro')
                ->first();

            if (!$abierto) {
                ReporteContingencia::create([
                    'tipo_evento' => 'incendio',
                    'temperatura_pico' => $datos['temperatura'],
                    'fecha_siniestro' => now(),
                ]);
            } elseif ($datos['temperatura'] > $abierto->temperatura_pico) {
                $abierto->update([
                    'temperatura_pico' => $datos['temperatura'],
                ]);
            }

        } else {
            $abierto = ReporteContingencia::where('tipo_evento', 'incendio')
                ->whereNull('duracion_segundos')
                ->latest('fecha_siniestro')
                ->first();

            if ($abierto) {
                $abierto->update([
                    'duracion_segundos' => abs(
                        now()->diffInSeconds($abierto->fecha_siniestro)
                    ),
                ]);
            }
        }

        return response()->json(['message' => 'Alerta registrada'], 201);
    }

    /**
     * Lista los incidentes de contingencia registrados.
     */
    public function index(): JsonResponse
    {
        $reportes = ReporteContingencia::orderByDesc('fecha_siniestro')
            ->limit(20)
            ->get();

        return response()->json($reportes);
    }
}