<?php

namespace App\Http\Controllers;

use App\Models\ReporteSalud;
use App\Services\ReporteSaludService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ReporteSaludController extends Controller
{
    public function __construct(
        private readonly ReporteSaludService $reporteSaludService
    ) {}

    public function index(): JsonResponse
    {
        $reportes = ReporteSalud::query()
            ->orderByDesc('fecha')
            ->get()
            ->map(function ($reporte) {
                return [
                    'id' => $reporte->id,
                    'fecha' => $reporte->fecha->format('Y-m-d'),
                    'temperatura_promedio' => (float) $reporte->temperatura_promedio,
                    'temperatura_min' => (float) $reporte->temperatura_min,
                    'temperatura_max' => (float) $reporte->temperatura_max,
                    'humedad_promedio' => (float) $reporte->humedad_promedio,
                    'diagnostico_ia' => $reporte->diagnostico_ia,
                ];
            });

        return response()->json($reportes);
    }

    public function generar(Request $request): JsonResponse
    {
        try {
            $datos = $request->validate([
                'fecha' => [
                    'required',
                    'date_format:Y-m-d',
                ],
            ]);

            $reporte = $this->reporteSaludService->generarParaFecha($datos['fecha']);

            return response()->json([
                'message' => 'Reporte generado correctamente',
                'reporte' => [
                    'id' => $reporte->id,
                    'fecha' => $reporte->fecha->format('Y-m-d'),
                    'temperatura_promedio' => (float) $reporte->temperatura_promedio,
                    'temperatura_min' => (float) $reporte->temperatura_min,
                    'temperatura_max' => (float) $reporte->temperatura_max,
                    'humedad_promedio' => (float) $reporte->humedad_promedio,
                    'diagnostico_ia' => $reporte->diagnostico_ia,
                ],
            ], 201);

        } catch (ValidationException $error) {
            throw $error;

        } catch (RuntimeException $error) {
            return response()->json([
                'message' => $error->getMessage(),
            ], 422);

        } catch (\Throwable $error) {
            Log::error(
                'Error generando reporte de salud',
                [
                    'message' => $error->getMessage(),
                    'fecha' => $request->input('fecha'),
                ]
            );

            return response()->json([
                'message' => 'No se pudo generar el reporte',
            ], 500);
        }
    }
}