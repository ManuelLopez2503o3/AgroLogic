<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Lista de todos los usuarios (solo admin).
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'administrador') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $usuarios = User::select('id', 'name', 'email', 'role', 'created_at')
            ->orderBy('name')
            ->get();

        return response()->json($usuarios);
    }

    /**
     * Cambiar el rol de un usuario (solo admin).
     */
    public function updateRole(Request $request, int $id): JsonResponse
    {
        if ($request->user()->role !== 'administrador') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $datos = $request->validate([
            'role' => ['required', 'in:administrador,operador'],
        ]);

        $usuario = User::findOrFail($id);
        $usuario->update(['role' => $datos['role']]);

        return response()->json([
            'message' => 'Rol actualizado',
            'usuario' => $usuario,
        ]);
    }

    /**
     * Eliminar un usuario (solo admin).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($request->user()->role !== 'administrador') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($request->user()->id === $id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propia cuenta',
            ], 422);
        }

        $usuario = User::findOrFail($id);
        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado']);
    }
}