<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\DecisionIA;
use App\Models\Telemetria;
use App\Services\AgenteIAService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgenteIAController extends Controller
{
    public function __construct(
        private readonly AgenteIAService $agenteIAService
    ) {}

    /**
     * CA-05: estado actual del agente + última decisión,
     * para pintar el panel "AgroLogic AI" en el Dashboard.
     */
    public function estado(): JsonResponse
    {
        $ultimaDecision = DecisionIA::query()
            ->latest('fecha_hora')
            ->first();

        return response()->json([
            'modo_autonomo' => $this->agenteIAService->modoAutonomoActivo(),

            'ultima_decision' => $ultimaDecision ? [
                'id' => $ultimaDecision->id,
                'fecha_hora' => $ultimaDecision->fecha_hora->format('Y-m-d H:i:s'),
                'nivel_riesgo' => $ultimaDecision->nivel_riesgo,
                'temperatura' => (float) $ultimaDecision->temperatura,
                'humedad' => (float) $ultimaDecision->humedad,
                'accion_ejecutada' => $ultimaDecision->accion_ejecutada,
                'motivo' => $ultimaDecision->motivo,
                'ejecutada_automaticamente' => $ultimaDecision->ejecutada_automaticamente,
            ] : null,
        ]);
    }

    /**
     * CA-06: activar/desactivar el modo autónomo (solo admin,
     * ya protegido por el middleware del grupo de rutas).
     */
    public function toggle(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'activo' => ['required', 'boolean'],
        ]);

        Configuracion::updateOrCreate(
            ['clave' => 'modo_autonomo'],
            ['valor' => $datos['activo'] ? '1' : '0']
        );

        return response()->json([
            'message' => $datos['activo']
                ? 'Modo agente activado'
                : 'Modo agente desactivado',
            'modo_autonomo' => $datos['activo'],
        ]);
    }

    /**
     * POST /api/ia/evaluar
     * Dispara manualmente una evaluación (para pruebas, o
     * como respaldo si algo falló en el flujo automático).
     * Si no se envían temperatura/humedad, usa la última
     * telemetría registrada.
     */
    public function evaluar(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'temperatura' => ['sometimes', 'numeric'],
            'humedad' => ['sometimes', 'numeric'],
        ]);

        if (!isset($datos['temperatura']) || !isset($datos['humedad'])) {
            $ultima = Telemetria::query()->latest('id')->first();

            if (!$ultima) {
                return response()->json([
                    'message' => 'No hay telemetría disponible para evaluar.',
                ], 422);
            }

            $datos['temperatura'] = (float) $ultima->temperatura;
            $datos['humedad'] = (float) $ultima->humedad;
        }

        $decision = $this->agenteIAService->evaluar(
            (float) $datos['temperatura'],
            (float) $datos['humedad']
        );

        return response()->json([
            'message' => 'Evaluación completada',
            'decision' => [
                'id' => $decision->id,
                'fecha_hora' => $decision->fecha_hora->format('Y-m-d H:i:s'),
                'nivel_riesgo' => $decision->nivel_riesgo,
                'accion_ejecutada' => $decision->accion_ejecutada,
                'motivo' => $decision->motivo,
            ],
        ], 201);
    }
}