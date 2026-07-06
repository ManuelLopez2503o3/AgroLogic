<?php

namespace App\Http\Controllers;

use App\Models\Telemetria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TelemetriaController extends Controller
{
    /**
     * Guardar una nueva lectura enviada
     * por serial_bridge.py.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'temperatura' => [
                'required',
                'numeric',
                'between:-50,100',
            ],

            'humedad' => [
                'required',
                'numeric',
                'between:0,100',
            ],
        ]);

        try {
            $estado = $this->determinarEstado(
                (float) $datos['temperatura'],
                (float) $datos['humedad']
            );

            $telemetria = Telemetria::create([
                'temperatura' => $datos['temperatura'],
                'humedad' => $datos['humedad'],
                'estado' => $estado,
                'fecha_registro' => now(),
            ]);

            return response()->json([
                'message' => 'Telemetría guardada correctamente',
                'id' => $telemetria->id,
                'estado' => $telemetria->estado,
            ], 201);

        } catch (\Throwable $error) {
            Log::error(
                'Error guardando telemetría',
                [
                    'message' => $error->getMessage(),
                ]
            );

            return response()->json([
                'message' => 'No se pudo guardar la telemetría',
            ], 500);
        }
    }


    /**
     * Obtener la lectura más reciente.
     */
    public function ultima(): JsonResponse
    {
        try {
            $telemetria = Telemetria::query()
                ->orderByDesc('id')
                ->first();

            if (!$telemetria) {
                return response()->json([
                    'message' => 'No hay telemetrías registradas',
                ], 404);
            }

            return response()->json([
                'id' => $telemetria->id,

                'temperatura' => (float)
                    $telemetria->temperatura,

                'humedad' => (float)
                    $telemetria->humedad,

                'estado' =>
                    $telemetria->estado,

                'fecha_registro' =>
                    optional(
                        $telemetria->fecha_registro
                    )->format('Y-m-d H:i:s'),
            ]);

        } catch (\Throwable $error) {
            Log::error(
                'Error obteniendo última telemetría',
                [
                    'message' => $error->getMessage(),
                ]
            );

            return response()->json([
                'message' =>
                    'No se pudo obtener la última telemetría',
            ], 500);
        }
    }


    /**
     * Obtener promedios históricos por hora
     * para una fecha seleccionada.
     *
     * Ejemplo:
     * /api/telemetrias/historico?fecha=2026-07-05
     */
    public function historico(Request $request): JsonResponse
    {
        try {
            $datosValidados = $request->validate([
                'fecha' => [
                    'required',
                    'date_format:Y-m-d',
                ],
            ]);

            $fecha = $datosValidados['fecha'];

            /*
             * Eloquent + SQL agregado.
             *
             * Agrupa todas las lecturas de la fecha
             * seleccionada por hora y calcula:
             *
             * - promedio de temperatura
             * - promedio de humedad
             * - cantidad de lecturas
             */
            $registros = Telemetria::query()
                ->selectRaw(
                    '
                    HOUR(fecha_registro) AS hora,
                    ROUND(AVG(temperatura), 2)
                        AS temperatura_promedio,
                    ROUND(AVG(humedad), 2)
                        AS humedad_promedio,
                    COUNT(*) AS total_lecturas
                    '
                )
                ->whereDate(
                    'fecha_registro',
                    $fecha
                )
                ->groupByRaw(
                    'HOUR(fecha_registro)'
                )
                ->orderByRaw(
                    'HOUR(fecha_registro) ASC'
                )
                ->get();

            /*
             * JSON limpio para Chart.js.
             */
            $labels = $registros
                ->map(function ($registro) {
                    return str_pad(
                        (string) $registro->hora,
                        2,
                        '0',
                        STR_PAD_LEFT
                    ) . ':00';
                })
                ->values();

            $temperaturas = $registros
                ->map(function ($registro) {
                    return round(
                        (float)
                        $registro->temperatura_promedio,
                        2
                    );
                })
                ->values();

            $humedades = $registros
                ->map(function ($registro) {
                    return round(
                        (float)
                        $registro->humedad_promedio,
                        2
                    );
                })
                ->values();

            $lecturasPorHora = $registros
                ->map(function ($registro) {
                    return (int)
                        $registro->total_lecturas;
                })
                ->values();

            return response()->json([
                'fecha' => $fecha,

                'resumen' => [
                    'total_horas' =>
                        $registros->count(),

                    'total_lecturas' =>
                        $registros->sum(
                            'total_lecturas'
                        ),
                ],

                'labels' => $labels,

                'datasets' => [
                    'temperatura' =>
                        $temperaturas,

                    'humedad' =>
                        $humedades,
                ],

                'lecturas_por_hora' =>
                    $lecturasPorHora,
            ]);

        } catch (ValidationException $error) {
            throw $error;

        } catch (\Throwable $error) {
            Log::error(
                'Error obteniendo histórico',
                [
                    'message' =>
                        $error->getMessage(),

                    'fecha' =>
                        $request->query('fecha'),
                ]
            );

            return response()->json([
                'message' =>
                    'No se pudo obtener el histórico',
            ], 500);
        }
    }


    /**
     * Determinar estado básico del invernadero.
     */
    private function determinarEstado(
        float $temperatura,
        float $humedad
    ): string {
        if ($temperatura >= 50) {
            return 'modo_fuego';
        }

        if (
            $temperatura >= 35 ||
            $humedad < 20 ||
            $humedad > 90
        ) {
            return 'alerta';
        }

        return 'normal';
    }
}