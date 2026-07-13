<?php

namespace App\Support\Pdf;

class BitacoraTemplate
{
    public static function render($telemetrias, $incidentes, string $fecha): string
    {
        $Logo = public_path('images/Logo.png');
        $fechaGeneracion = now()->format('d/m/Y H:i');
        $fechaBonita = \Carbon\Carbon::parse($fecha)->format('d \d\e F \d\e Y');

        return '<!DOCTYPE html><html><head><meta charset="utf-8">'
            . self::estilos()
            . '</head><body>'
            . self::encabezado($Logo, $fechaGeneracion, $fechaBonita)
            . self::piePagina()
            . self::seccionIncidentes($incidentes)
            . self::seccionTelemetrias($telemetrias)
            . '</body></html>';
    }

    private static function estilos(): string
    {
        return '<style>
            @page { margin: 90px 40px 60px 40px; }
            body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1a1a1a; }
            header { position: fixed; top: -70px; left: 0; right: 0; height: 70px; border-bottom: 2px solid #15803d; padding-bottom: 10px; }
            header img { height: 48px; float: left; }
            header .titulo { float: left; margin-left: 14px; padding-top: 6px; }
            header .titulo h1 { margin: 0; font-size: 18px; color: #14532d; }
            header .titulo span { font-size: 10px; color: #5d7d68; }
            header .fecha { float: right; padding-top: 14px; font-size: 10px; color: #5d7d68; }
            footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 30px; text-align: center; font-size: 9px; color: #8a9a91; border-top: 1px solid #d8e8de; padding-top: 8px; }
            h2.seccion { font-size: 14px; color: #14532d; border-bottom: 1px solid #b7dfc4; padding-bottom: 4px; margin-top: 26px; margin-bottom: 10px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
            th { background: #eaf6ec; color: #14532d; text-align: left; padding: 6px 8px; font-size: 10px; border-bottom: 1px solid #b7dfc4; }
            td { padding: 6px 8px; font-size: 10px; border-bottom: 1px solid #eef2ef; vertical-align: top; }
            .estado-normal { color: #15803d; }
            .estado-alerta { color: #b45309; }
            .estado-modo_fuego { color: #b91c1c; font-weight: bold; }
            .reporte-ia { font-size: 9.5px; color: #333; line-height: 1.5; }
            .sin-datos { color: #8a9a91; font-style: italic; font-size: 10px; }
        </style>';
    }

    private static function encabezado(string $Logo, string $fechaGeneracion, string $fechaBonita): string
    {
        return '<header>
            <img src="' . $Logo . '">
            <div class="titulo">
                <h1>AgroLogic</h1>
                <span>Bitácora del ' . $fechaBonita . '</span>
            </div>
            <div class="fecha">Generado: ' . $fechaGeneracion . '</div>
        </header>';
    }

    private static function piePagina(): string
    {
        return '<footer>AgroLogic &mdash; Sistema de invernadero inteligente</footer>';
    }

    private static function seccionIncidentes($incidentes): string
    {
        $html = '<h2 class="seccion">Incidentes de Modo Fuego</h2>';

        if ($incidentes->count() === 0) {
            return $html . '<p class="sin-datos">No se registraron incidentes ese día.</p>';
        }

        foreach ($incidentes as $incidente) {
            $fechaInicio = \Carbon\Carbon::parse($incidente->fecha_inicio)->format('d/m/Y H:i');
            $duracion = $incidente->duracion_segundos !== null
                ? round($incidente->duracion_segundos / 60, 1) . ' min'
                : 'En curso';
            $humedad = $incidente->humedad_final ?? '—';
            $estado = $incidente->activo ? 'Activo' : 'Cerrado';

            $html .= '<table>
                <tr>
                    <th style="width:22%">Fecha inicio</th>
                    <th style="width:15%">Pico</th>
                    <th style="width:15%">Humedad final</th>
                    <th style="width:15%">Duración</th>
                    <th style="width:33%">Estado</th>
                </tr>
                <tr>
                    <td>' . $fechaInicio . '</td>
                    <td>' . $incidente->pico_temperatura . '°C</td>
                    <td>' . $humedad . '%</td>
                    <td>' . $duracion . '</td>
                    <td>' . $estado . '</td>
                </tr>
            </table>';

            if ($incidente->reporte_ia) {
                $html .= '<p class="reporte-ia">' . e($incidente->reporte_ia) . '</p>';
            }
        }

        return $html;
    }

    private static function seccionTelemetrias($telemetrias): string
    {
        $html = '<h2 class="seccion">Registros de Telemetría</h2>';

        if ($telemetrias->count() === 0) {
            return $html . '<p class="sin-datos">No hay registros de telemetría para ese día.</p>';
        }

        $html .= '<table>
            <tr>
                <th style="width:30%">Fecha</th>
                <th style="width:23%">Temperatura</th>
                <th style="width:23%">Humedad</th>
                <th style="width:24%">Estado</th>
            </tr>';

        foreach ($telemetrias as $t) {
            $fechaT = \Carbon\Carbon::parse($t->fecha_registro)->format('d/m/Y H:i:s');

            $html .= '<tr>
                <td>' . $fechaT . '</td>
                <td>' . $t->temperatura . '°C</td>
                <td>' . $t->humedad . '%</td>
                <td class="estado-' . $t->estado . '">' . $t->estado . '</td>
            </tr>';
        }

        return $html . '</table>';
    }
}