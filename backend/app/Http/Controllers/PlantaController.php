<?php

namespace App\Http\Controllers;

use App\Models\Planta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PlantaController extends Controller
{
    private const LIMITE_PLANTAS_POR_USUARIO = 5;

    // GET /api/plantas
    public function index()
    {
        $plantas = Planta::where('user_id', Auth::id())->get();

        return response()->json($plantas);
    }

    // POST /api/plantas
    public function store(Request $request)
    {
        $userId = Auth::id();

        $totalUsuario = Planta::where('user_id', $userId)->count();

        if ($totalUsuario >= self::LIMITE_PLANTAS_POR_USUARIO) {
            return response()->json([
                'message' => 'No puedes registrar más de ' . self::LIMITE_PLANTAS_POR_USUARIO . ' plantas.'
            ], 422);
        }

        $validator = $this->validarPlanta($request, false);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $datos = $validator->validated();
        $datos['user_id'] = $userId;

        $planta = Planta::create($datos);

        return response()->json($planta, 201);
    }

    // GET /api/plantas/{id}
    public function show(Planta $planta)
    {
        $this->autorizarPropietario($planta);

        return response()->json($planta);
    }

    // PUT/PATCH /api/plantas/{id}
    public function update(Request $request, Planta $planta)
    {
        $this->autorizarPropietario($planta);

        $validator = $this->validarPlanta($request, true);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $planta->update($validator->validated());

        return response()->json($planta);
    }

    // DELETE /api/plantas/{id}
    public function destroy(Planta $planta)
    {
        $this->autorizarPropietario($planta);

        $planta->delete();

        return response()->json(['message' => 'Planta eliminada correctamente.']);
    }

    /**
     * Verifica que la planta pertenezca al usuario autenticado.
     */
    private function autorizarPropietario(Planta $planta)
    {
        if ($planta->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para acceder a esta planta.');
        }
    }
    // POST /api/plantas/sugerir
    public function sugerir(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        $nombre = $request->input('nombre');

        try {
            $respuesta = Http::withToken(env('OPENAI_API_KEY'))
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un experto en agricultura e invernaderos. Dado el nombre de una planta, responde ÚNICAMENTE con un JSON válido (sin texto adicional, sin markdown, sin ```) con esta estructura exacta: {"humedad_min": number, "humedad_max": number, "temperatura_min": number, "temperatura_max": number, "nutrientes_recomendados": "string corto", "descripcion": "string corto con un consejo de cuidado"}. humedad_min y humedad_max son porcentajes entre 0 y 100 (humedad_max >= humedad_min). temperatura_min y temperatura_max son grados Celsius entre -10 y 60 (temperatura_max >= temperatura_min).',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Dame los rangos ideales de cultivo para: {$nombre}",
                        ],
                    ],
                    'temperature' => 0.4,
                ]);

            if (!$respuesta->successful()) {
                return response()->json([
                    'message' => 'No se pudo generar la sugerencia con IA.'
                ], 502);
            }

            $contenido = trim($respuesta->json('choices.0.message.content') ?? '');
            $contenido = preg_replace('/^```json|```$/m', '', $contenido);
            $contenido = trim($contenido);

            $datos = json_decode($contenido, true);

            if (json_last_error() !== JSON_ERROR_NONE || !$datos) {
                return response()->json([
                    'message' => 'La IA no devolvió un formato válido, intenta de nuevo.'
                ], 502);
            }

            return response()->json([
                'humedad_min' => $datos['humedad_min'] ?? null,
                'humedad_max' => $datos['humedad_max'] ?? null,
                'temperatura_min' => $datos['temperatura_min'] ?? null,
                'temperatura_max' => $datos['temperatura_max'] ?? null,
                'nutrientes_recomendados' => $datos['nutrientes_recomendados'] ?? null,
                'descripcion' => $datos['descripcion'] ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error generando la sugerencia.'
            ], 500);
        }
    }

    /**
     * Valida los datos de una planta.
     * $esUpdate = true permite campos parciales (sometimes).
     */
    private function validarPlanta(Request $request, bool $esUpdate)
    {
        $regla = $esUpdate ? 'sometimes|required' : 'required';

        return Validator::make($request->all(), [
            'nombre' => "$regla|string|max:100",
            'humedad_min' => "$regla|numeric|min:0|max:100",
            'humedad_max' => "$regla|numeric|min:0|max:100|gte:humedad_min",
            'temperatura_min' => "$regla|numeric|min:-10|max:60",
            'temperatura_max' => "$regla|numeric|min:-10|max:60|gte:temperatura_min",
            'nutrientes_recomendados' => "$regla|string|max:1000",
            'descripcion' => 'nullable|string|max:1000',
        ]);
    }
}