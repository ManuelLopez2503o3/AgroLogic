<?php

namespace App\Services;

use App\Models\Actuador;
use App\Models\Configuracion;
use App\Models\DecisionIA;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AgenteIAService
{
    /**
     * CA-01/CA-02/CA-03: analiza la lectura actual, clasifica
     * el nivel de riesgo y, si el modo autónomo está activo y
     * el riesgo es crítico, ejecuta acciones sobre los actuadores.
     */
    public function evaluar(float $temperatura, float $humedad): DecisionIA
    {
        $temperaturaMaxima = (float) (
            Configuracion::where('clave', 'temperatura_maxima')->value('valor')
            ?? 35
        );

        $nivelRiesgo = $this->clasificarRiesgo(
            $temperatura,
            $humedad,
            $temperaturaMaxima
        );

        $accionEjecutada = null;
        $ejecutadaAutomaticamente = false;

        if ($nivelRiesgo === 'critico' && $this->modoAutonomoActivo()) {
            $accionEjecutada = $this->ejecutarAccionCritica();
            $ejecutadaAutomaticamente = true;
        }

        $motivo = $this->generarMotivo(
            $nivelRiesgo,
            $temperatura,
            $humedad,
            $temperaturaMaxima,
            $accionEjecutada
        );

        return DecisionIA::create([
            'fecha_hora' => now(),
            'nivel_riesgo' => $nivelRiesgo,
            'temperatura' => $temperatura,
            'humedad' => $humedad,
            'accion_ejecutada' => $accionEjecutada,
            'motivo' => $motivo,
            'ejecutada_automaticamente' => $ejecutadaAutomaticamente,
        ]);
    }

    /**
     * CA-02: normal / advertencia / crítico.
     */
    private function clasificarRiesgo(
        float $temperatura,
        float $humedad,
        float $temperaturaMaxima
    ): string {
        if (
            $temperatura >= $temperaturaMaxima ||
            $humedad < 20 ||
            $humedad > 90
        ) {
            return 'critico';
        }

        if (
            $temperatura >= ($temperaturaMaxima - 5) ||
            $humedad < 30 ||
            $humedad > 80
        ) {
            return 'advertencia';
        }

        return 'normal';
    }

    /**
     * CA-03/CA-08: acción de mitigación en un escenario crítico.
     * Enciende la bomba de riego y apaga las luces para reducir
     * la carga de calor. Respeta los límites: solo actúa sobre
     * los dos actuadores existentes, sin lógica adicional.
     */
    private function ejecutarAccionCritica(): string
    {
        $actuador = Actuador::first();

        if (!$actuador) {
            return 'No se pudo ejecutar: no hay actuador registrado.';
        }

        $actuador->update([
            'bomba_status' => true,
            'luces_status' => false,
        ]);

        return 'Bomba de riego encendida y luces apagadas automáticamente.';
    }

    /**
     * CA-06: consulta si el modo autónomo está activo.
     */
    public function modoAutonomoActivo(): bool
    {
        $valor = Configuracion::where('clave', 'modo_autonomo')->value('valor');

        return $valor === '1' || $valor === 1 || $valor === true;
    }

    /**
     * CA-07: genera la explicación técnica legible de la decisión.
     * Solo llama a OpenAI cuando hay algo relevante que explicar
     * (advertencia o crítico), para no gastar la API en cada
     * lectura normal.
     */
    private function generarMotivo(
        string $nivelRiesgo,
        float $temperatura,
        float $humedad,
        float $temperaturaMaxima,
        ?string $accionEjecutada
    ): string {
        if ($nivelRiesgo === 'normal') {
            return "Condiciones dentro de rango normal (temperatura {$temperatura}°C, humedad {$humedad}%).";
        }

        try {
            $contextoAccion = $accionEjecutada
                ? "Se ejecutó esta acción automática: {$accionEjecutada}"
                : "No se ejecutó ninguna acción automática (modo autónomo desactivado o riesgo no crítico).";

            $prompt = "Eres el agente de IA de un sistema de invernadero "
                . "inteligente (AgroLogic). Se detectó un nivel de riesgo "
                . "'{$nivelRiesgo}' con estos datos:\n"
                . "- Temperatura: {$temperatura} °C (umbral configurado: {$temperaturaMaxima} °C)\n"
                . "- Humedad: {$humedad}%\n"
                . "- {$contextoAccion}\n\n"
                . "Redacta una explicación técnica breve (máximo 60 palabras) "
                . "de por qué se clasificó así y qué implica para el cultivo. "
                . "Tono profesional y directo. Responde SOLO con la explicación, "
                . "sin título, encabezado, firma ni placeholders.";

            $respuesta = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            return $respuesta->choices[0]->message->content;

        } catch (\Throwable $error) {
            Log::error(
                'Error generando motivo del agente IA',
                ['message' => $error->getMessage()]
            );

            return "Riesgo {$nivelRiesgo} detectado (temperatura {$temperatura}°C, humedad {$humedad}%).";
        }
    }
}