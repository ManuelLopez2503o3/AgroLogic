<?php

namespace App\Services;

use App\Models\ReporteSalud;
use App\Models\Telemetria;
use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;

class ReporteSaludService
{
    /**
     * Genera (o regenera) el reporte de salud para una
     * fecha, a partir del promedio de telemetría de ese
     * día, usando IA.
     *
     * @throws RuntimeException si no hay lecturas para la fecha.
     */
    public function generarParaFecha(string $fecha): ReporteSalud
    {
        $stats = Telemetria::query()
            ->whereDate('fecha_registro', $fecha)
            ->selectRaw('
                ROUND(AVG(temperatura), 2) AS temperatura_promedio,
                ROUND(MIN(temperatura), 2) AS temperatura_min,
                ROUND(MAX(temperatura), 2) AS temperatura_max,
                ROUND(AVG(humedad), 2) AS humedad_promedio,
                COUNT(*) AS total_lecturas
            ')
            ->first();

        if (!$stats || (int) $stats->total_lecturas === 0) {
            throw new RuntimeException(
                "No hay lecturas de telemetría para la fecha {$fecha}."
            );
        }

        $diagnostico = $this->generarDiagnosticoIA(
            $fecha,
            (float) $stats->temperatura_promedio,
            (float) $stats->temperatura_min,
            (float) $stats->temperatura_max,
            (float) $stats->humedad_promedio
        );

        return ReporteSalud::updateOrCreate(
            ['fecha' => $fecha],
            [
                'temperatura_promedio' => $stats->temperatura_promedio,
                'temperatura_min' => $stats->temperatura_min,
                'temperatura_max' => $stats->temperatura_max,
                'humedad_promedio' => $stats->humedad_promedio,
                'diagnostico_ia' => $diagnostico,
            ]
        );
    }

    private function generarDiagnosticoIA(
        string $fecha,
        float $temperaturaPromedio,
        float $temperaturaMin,
        float $temperaturaMax,
        float $humedadPromedio
    ): string {
        $prompt = "Eres un asistente técnico de un sistema de "
            . "invernadero inteligente (AgroLogic). Estos son los "
            . "datos ambientales promedio del día {$fecha}:\n"
            . "- Temperatura promedio: {$temperaturaPromedio} °C\n"
            . "- Temperatura mínima: {$temperaturaMin} °C\n"
            . "- Temperatura máxima: {$temperaturaMax} °C\n"
            . "- Humedad promedio: {$humedadPromedio}%\n\n"
            . "Redacta un diagnóstico técnico breve (máximo 100 "
            . "palabras) sobre posibles riesgos de estrés hídrico "
            . "o condiciones fuera de rango para las plantas del "
            . "invernadero. Tono profesional y directo. Responde "
            . "SOLO con el análisis, en párrafos corridos. No "
            . "incluyas título, encabezado, fecha, firma, nombre, "
            . "cargo, ni placeholders entre corchetes.";

        $respuesta = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $respuesta->choices[0]->message->content;
    }
}