<?php

namespace App\Http\Controllers;

use App\Models\IncidenteFuego;
use App\Models\Telemetria;
use App\Support\Pdf\BitacoraTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BitacoraController extends Controller
{
    public function exportarPdf(Request $request): Response
    {
        if ($request->user()->role !== 'administrador') {
            abort(403, 'No autorizado');
        }

        $fecha = $request->query('fecha', now()->format('Y-m-d'));

        $telemetrias = Telemetria::whereDate('fecha_registro', $fecha)
            ->orderBy('fecha_registro')
            ->get();

        $incidentes = IncidenteFuego::whereDate('fecha_inicio', $fecha)
            ->orderBy('fecha_inicio')
            ->get();

        $html = BitacoraTemplate::render($telemetrias, $incidentes, $fecha);

        $pdf = Pdf::loadHTML($html)->setPaper('letter');

        return $pdf->stream('bitacora-agrologic-' . $fecha . '.pdf');
    }
}