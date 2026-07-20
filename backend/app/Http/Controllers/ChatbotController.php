<?php

namespace App\Http\Controllers;

use App\Models\Actuador;
use App\Models\ChatHistorial;
use App\Models\IncidenteFuego;
use App\Models\Planta;
use App\Models\Telemetria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;

class ChatbotController extends Controller
{
    public function preguntar(Request $request)
    {
        $request->validate([
            'pregunta' => 'required|string|max:500',
        ]);

        $pregunta = $request->input('pregunta');
        $usuario = $request->user();

        $plantas = Planta::all();
        $ultimaTelemetria = Telemetria::latest('fecha_registro')->first();

        $plantaDetectada = $plantas->first(function ($planta) use ($pregunta) {
            return Str::contains(Str::lower($pregunta), Str::lower($planta->nombre));
        });

        // =========================================
        // CONTEXTO DEL SISTEMA
        // =========================================

        $contexto = "Eres AgroBot, el asistente virtual de AgroLogic, un sistema de invernadero inteligente. ";
        $contexto .= "Ayudas a operadores y administradores con dudas sobre agricultura y con el control del invernadero.\n\n";

        // Alerta proactiva: si hay un incidente de fuego activo, se menciona siempre
        $incidenteActivo = IncidenteFuego::where('activo', 1)->latest('fecha_inicio')->first();
        if ($incidenteActivo) {
            $contexto .= "🚨 ALERTA ACTIVA — INCIDENTE DE FUEGO SIN RESOLVER:\n";
            $contexto .= "Iniciado el {$incidenteActivo->fecha_inicio}. Pico de temperatura: {$incidenteActivo->pico_temperatura}°C. ";
            if ($incidenteActivo->reporte_ia) {
                $contexto .= "Detalle: {$incidenteActivo->reporte_ia}. ";
            }
            $contexto .= "DEBES mencionar esto al usuario al inicio de tu respuesta, sin importar qué haya preguntado.\n\n";
        }

        if ($ultimaTelemetria) {
            $contexto .= "CONDICIONES ACTUALES DEL INVERNADERO (sensor DHT22):\n";
            $contexto .= "- Temperatura actual: {$ultimaTelemetria->temperatura}°C\n";
            $contexto .= "- Humedad actual: {$ultimaTelemetria->humedad}%\n";
            $contexto .= "- Estado del sistema: {$ultimaTelemetria->estado}\n";
            $contexto .= "- Última lectura registrada: {$ultimaTelemetria->fecha_registro}\n\n";
            $contexto .= "Si la última lectura tiene más de 15 minutos de antigüedad respecto a ahora, acláralo brevemente al usuario.\n\n";
        }

        $contexto .= "Las siguientes plantas están registradas, con sus parámetros ideales:\n\n";
        foreach ($plantas as $planta) {
            $contexto .= "- {$planta->nombre}: Humedad ideal {$planta->humedad_min}%-{$planta->humedad_max}%, ";
            $contexto .= "Temperatura ideal {$planta->temperatura_min}°C-{$planta->temperatura_max}°C. ";
            $contexto .= "Nutrientes: {$planta->nutrientes_recomendados}. ";
            if ($planta->descripcion) {
                $contexto .= "Nota: {$planta->descripcion}. ";
            }
            $contexto .= "\n";
        }

        $contexto .= "\nInstrucciones de comportamiento:\n";
        $contexto .= "1. Para datos IDEALES de una planta, usa exclusivamente los registrados arriba. No inventes valores.\n";
        $contexto .= "2. Compara los datos EN VIVO contra el rango ideal cuando te pregunten si las condiciones actuales son buenas.\n";
        $contexto .= "3. Puedes responder libremente preguntas generales de agricultura, aunque la planta no esté registrada.\n";
        $contexto .= "4. Tienes herramientas para CONSULTAR el estado de los actuadores (cualquier usuario) y para CONTROLARLOS (encender/apagar bomba o luces), pero controlar solo está permitido para administradores. Si un operador lo pide, intenta la función de todos modos: el sistema te dirá si no tiene permiso, y así se lo explicas amablemente al usuario.\n";
        $contexto .= "5. Ten en cuenta el historial reciente de la conversación.\n";
        $contexto .= "6. Sé breve, claro y cercano.\n";
        $contexto .= "7. Si te preguntan cómo estuvo el día, cómo va el invernadero en general, o piden un resumen, usa la función resumen_del_dia en vez de responder solo con el dato más reciente.\n";

        // =========================================
        // MEMORIA (últimas 5 interacciones)
        // =========================================

        $historialReciente = ChatHistorial::where('user_id', $usuario->id)
            ->latest('created_at')
            ->take(5)
            ->get()
            ->reverse();

        $mensajes = [['role' => 'system', 'content' => $contexto]];

        foreach ($historialReciente as $item) {
            $mensajes[] = ['role' => 'user', 'content' => $item->pregunta];
            $mensajes[] = ['role' => 'assistant', 'content' => $item->respuesta];
        }

        $mensajes[] = ['role' => 'user', 'content' => $pregunta];

        // =========================================
        // DEFINICIÓN DE HERRAMIENTAS (FUNCTION CALLING)
        // =========================================

        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'consultar_actuadores',
                    'description' => 'Consulta si la bomba de agua y las luces del invernadero están actualmente encendidas o apagadas.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass(),
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'controlar_bomba',
                    'description' => 'Enciende o apaga la bomba de riego del invernadero. Solo administradores pueden ejecutar esta acción.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'estado' => [
                                'type' => 'string',
                                'enum' => ['encender', 'apagar'],
                                'description' => 'Acción a realizar sobre la bomba.',
                            ],
                        ],
                        'required' => ['estado'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'controlar_luces',
                    'description' => 'Enciende o apaga las luces generales del invernadero. Solo administradores pueden ejecutar esta acción.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'estado' => [
                                'type' => 'string',
                                'enum' => ['encender', 'apagar'],
                                'description' => 'Acción a realizar sobre las luces.',
                            ],
                        ],
                        'required' => ['estado'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'resumen_del_dia',
                    'description' => 'Genera un resumen ejecutivo de las condiciones del invernadero durante el día de hoy: temperatura, humedad, incidentes de fuego y estado actual de los actuadores.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass(),
                    ],
                ],
            ],
        ];

        // =========================================
        // PRIMERA LLAMADA A OPENAI (con herramientas)
        // =========================================

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => $mensajes,
            'tools' => $tools,
            'tool_choice' => 'auto',
        ]);

        $mensajeRespuesta = $response->choices[0]->message;

        // Si el modelo NO pidió ejecutar ninguna función, respondemos directo
        if (empty($mensajeRespuesta->toolCalls)) {
            $respuestaFinal = $mensajeRespuesta->content;
        } else {
            // --- El modelo pidió ejecutar una o más funciones ---
            $mensajes[] = [
                'role' => 'assistant',
                'content' => $mensajeRespuesta->content,
                'tool_calls' => array_map(fn ($tc) => [
                    'id' => $tc->id,
                    'type' => $tc->type,
                    'function' => [
                        'name' => $tc->function->name,
                        'arguments' => $tc->function->arguments,
                    ],
                ], $mensajeRespuesta->toolCalls),
            ];

            foreach ($mensajeRespuesta->toolCalls as $toolCall) {
                $nombreFuncion = $toolCall->function->name;
                $argumentos = json_decode($toolCall->function->arguments, true) ?? [];

                $resultado = $this->ejecutarFuncion($nombreFuncion, $argumentos, $usuario);

                $mensajes[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall->id,
                    'content' => json_encode($resultado),
                ];
            }

            // --- Segunda llamada: el modelo redacta la respuesta final con los resultados ---
            $segundaRespuesta = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => $mensajes,
            ]);

            $respuestaFinal = $segundaRespuesta->choices[0]->message->content;
        }

        // =========================================
        // GUARDAR EN HISTORIAL
        // =========================================

        ChatHistorial::create([
            'user_id' => $usuario->id,
            'planta_id' => $plantaDetectada?->id,
            'pregunta' => $pregunta,
            'respuesta' => $respuestaFinal,
        ]);

        return response()->json([
            'pregunta' => $pregunta,
            'respuesta' => $respuestaFinal,
        ]);
    }

    /**
     * Ejecuta la función solicitada por el modelo y devuelve un array
     * que luego se serializa como resultado de la herramienta.
     */
    private function ejecutarFuncion(string $nombre, array $argumentos, $usuario): array
    {
        $actuador = Actuador::first();

        if (!$actuador) {
            return ['error' => 'No hay actuadores configurados en el sistema.'];
        }

        switch ($nombre) {

            case 'consultar_actuadores':
                return [
                    'bomba' => $actuador->bomba_status ? 'encendida' : 'apagada',
                    // luces_status = true significa APAGADAS en este sistema
                    'luces' => $actuador->luces_status ? 'apagadas' : 'encendidas',
                ];

            case 'controlar_bomba':
                if ($usuario->role !== 'administrador') {
                    return ['error' => 'No autorizado. Solo un administrador puede controlar la bomba.'];
                }

                $nuevoEstado = $argumentos['estado'] === 'encender';
                $actuador->update(['bomba_status' => $nuevoEstado]);

                return [
                    'ok' => true,
                    'bomba' => $nuevoEstado ? 'encendida' : 'apagada',
                ];

            case 'controlar_luces':
                if ($usuario->role !== 'administrador') {
                    return ['error' => 'No autorizado. Solo un administrador puede controlar las luces.'];
                }

                // Lógica invertida: encender => luces_status = false
                $nuevoEstadoCampo = $argumentos['estado'] === 'apagar';
                $actuador->update(['luces_status' => $nuevoEstadoCampo]);

                return [
                    'ok' => true,
                    'luces' => $argumentos['estado'] === 'encender' ? 'encendidas' : 'apagadas',
                ];

            case 'resumen_del_dia':
                $registrosHoy = Telemetria::whereDate('fecha_registro', now()->toDateString())
                    ->get();

                if ($registrosHoy->isEmpty()) {
                    return ['mensaje' => 'No hay lecturas de temperatura/humedad registradas el día de hoy todavía.'];
                }

                $incidentesHoy = IncidenteFuego::whereDate('fecha_inicio', now()->toDateString())->get();
                $incidentesActivosAhora = IncidenteFuego::where('activo', 1)->count();

                return [
                    'fecha' => now()->toDateString(),
                    'total_lecturas_hoy' => $registrosHoy->count(),
                    'temperatura_promedio' => round($registrosHoy->avg('temperatura'), 1),
                    'temperatura_max' => $registrosHoy->max('temperatura'),
                    'temperatura_min' => $registrosHoy->min('temperatura'),
                    'humedad_promedio' => round($registrosHoy->avg('humedad'), 1),
                    'humedad_max' => $registrosHoy->max('humedad'),
                    'humedad_min' => $registrosHoy->min('humedad'),
                    'incidentes_fuego_hoy' => $incidentesHoy->count(),
                    'incidentes_fuego_activos_ahora' => $incidentesActivosAhora,
                    'bomba_estado_actual' => $actuador->bomba_status ? 'encendida' : 'apagada',
                    'luces_estado_actual' => $actuador->luces_status ? 'apagadas' : 'encendidas',
                ];

            default:
                return ['error' => "Función desconocida: {$nombre}"];
        }
    }

    public function historial(Request $request)
    {
        $usuario = $request->user();

        $historial = ChatHistorial::where('user_id', $usuario->id)
            ->orderBy('created_at', 'asc')
            ->take(50)
            ->get(['pregunta', 'respuesta', 'created_at']);

        return response()->json($historial);
    }
}