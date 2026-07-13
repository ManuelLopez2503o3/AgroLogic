<?php

namespace App\Http\Controllers;

use App\Models\IncidenteFuego;
use App\Models\Telemetria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Configuracion;

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

            $this->manejarIncidenteFuego(
                $estado,
                (float) $datos['temperatura'],
                (float) $datos['humedad']
            );

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

    $temperaturaMaxima = (float) (
        Configuracion::where('clave', 'temperatura_maxima')->value('valor')
        ?? 35
    );

    if (
        $temperatura >= $temperaturaMaxima ||
        $humedad < 20 ||
        $humedad > 90
    ) {
        return 'alerta';
    }

    return 'normal';
}

    /**
     * Detectar apertura/cierre de un episodio de
     * Modo Fuego y disparar el reporte de IA
     * cuando el episodio cierra (CA-01).
     */
    private function manejarIncidenteFuego(
        string $estado,
        float $temperatura,
        float $humedad
    ): void {
        $incidenteActivo = IncidenteFuego::where('activo', true)
            ->latest('fecha_inicio')
            ->first();

        if ($estado === 'modo_fuego') {
            if (!$incidenteActivo) {
                // Empieza un episodio nuevo
                IncidenteFuego::create([
                    'fecha_inicio' => now(),
                    'pico_temperatura' => $temperatura,
                    'activo' => true,
                ]);
                return;
            }

            // Sigue activo: actualizar el pico si aplica
            if ($temperatura > $incidenteActivo->pico_temperatura) {
                $incidenteActivo->update([
                    'pico_temperatura' => $temperatura,
                ]);
            }
            return;
        }

        // Ya no estamos en modo_fuego: si había uno activo, se cierra
        if ($incidenteActivo) {
            $duracionSegundos = abs(
    now()->diffInSeconds($incidenteActivo->fecha_inicio)
);

            $incidenteActivo->update([
                'fecha_fin' => now(),
                'humedad_final' => $humedad,
                'duracion_segundos' => $duracionSegundos,
                'activo' => false,
            ]);

            $this->generarReporteIA($incidenteActivo);
        }
    }


    /**
     * CA-02 / CA-03: arma el prompt con las variables
     * duras del episodio, llama a OpenAI, y guarda
     * el texto explicativo generado.
     */
    private function generarReporteIA(IncidenteFuego $incidente): void
    {
        try {
            $duracionMinutos = round(
                $incidente->duracion_segundos / 60,
                1
            );

            $prompt = $prompt = "Eres un asistente técnico de un sistema de "
    . "invernadero inteligente (AgroLogic). Se acaba de "
    . "cerrar un episodio de Modo Fuego con estos datos:\n"
    . "- Pico de temperatura: {$incidente->pico_temperatura} °C\n"
    . "- Duración del episodio: {$duracionMinutos} minutos\n"
    . "- Humedad final registrada: {$incidente->humedad_final}%\n\n"
    . "Redacta un reporte técnico breve (máximo 120 palabras) "
    . "describiendo el comportamiento del incidente y si el "
    . "riego automático parece haber sido efectivo para "
    . "controlar la situación. Tono profesional y directo. "
    . "Responde SOLO con el análisis, en párrafos corridos. "
    . "No incluyas título, encabezado tipo carta, fecha, "
    . "firma, nombre, cargo, ni ningún placeholder entre "
    . "corchetes como [Tu Nombre] o [Fecha].";

            $respuesta = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $texto = $respuesta->choices[0]->message->content;

            $incidente->update([
                'reporte_ia' => $texto,
            ]);

        } catch (\Throwable $error) {
            Log::error(
                'Error generando reporte IA de incidente de fuego',
                [
                    'incidente_id' => $incidente->id,
                    'message' => $error->getMessage(),
                ]
            );
        }
    }
}