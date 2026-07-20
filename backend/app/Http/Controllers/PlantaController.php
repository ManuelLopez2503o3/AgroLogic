<?php

namespace App\Http\Controllers;

use App\Models\Planta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PlantaController extends Controller
{
    // GET /api/plantas
    public function index()
    {
        return response()->json(Planta::all());
    }

    // POST /api/plantas
    public function store(Request $request)
    {
        if (!$this->esAdmin()) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if (Planta::count() >= 3) {
            return response()->json([
                'message' => 'No se pueden registrar más de 3 plantas.'
            ], 422);
        }

        $validator = $this->validarPlanta($request, false);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $planta = Planta::create($validator->validated());

        return response()->json($planta, 201);
    }

    // GET /api/plantas/{id}
    public function show(Planta $planta)
    {
        return response()->json($planta);
    }

    // PUT/PATCH /api/plantas/{id}
    public function update(Request $request, Planta $planta)
    {
        if (!$this->esAdmin()) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

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
        if (!$this->esAdmin()) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $planta->delete();

        return response()->json(['message' => 'Planta eliminada correctamente.']);
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

    /**
     * Verifica si el usuario autenticado tiene rol administrador.
     */
    private function esAdmin(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'administrador';
    }
}