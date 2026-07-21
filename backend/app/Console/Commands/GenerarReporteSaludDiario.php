<?php

namespace App\Console\Commands;

use App\Services\ReporteSaludService;
use Illuminate\Console\Command;
use RuntimeException;

class GenerarReporteSaludDiario extends Command
{
    protected $signature = 'reportes:generar-salud-diario';

    protected $description = 'Genera el reporte de salud (IA) del día anterior a partir del histórico de telemetría.';

    public function handle(ReporteSaludService $reporteSaludService): int
    {
        $fecha = now()->subDay()->format('Y-m-d');

        $this->info("Generando reporte de salud para {$fecha}...");

        try {
            $reporte = $reporteSaludService->generarParaFecha($fecha);

            $this->info("Reporte generado correctamente (id {$reporte->id}).");

            return self::SUCCESS;

        } catch (RuntimeException $error) {
            $this->warn($error->getMessage());

            return self::FAILURE;

        } catch (\Throwable $error) {
            $this->error("Error generando el reporte: {$error->getMessage()}");

            return self::FAILURE;
        }
    }
}