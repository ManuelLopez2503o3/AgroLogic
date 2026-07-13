<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComandoController;
use App\Http\Controllers\TelemetriaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncidenteFuegoController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActuadorController;
use App\Http\Controllers\AlertaIncendioController;
use App\Http\Controllers\BitacoraController;

// pública, la llama serial_bridge.py (maquina a maquina, prioridad alta)
Route::post('/alertas/incendio', [AlertaIncendioController::class, 'store']);


/*
|--------------------------------------------------------------------------
| AUTENTICACIÓN PÚBLICA
|--------------------------------------------------------------------------
*/

Route::post(
    '/register',
    [AuthController::class, 'register']
);

Route::post(
    '/login',
    [AuthController::class, 'login']
);

// pública, junto a /comandos/pendiente
Route::get('/actuadores/status', [ActuadorController::class, 'status']);



/*
|--------------------------------------------------------------------------
| TELEMETRÍA
|--------------------------------------------------------------------------
*/
Route::get('/alertas/incendio', [AlertaIncendioController::class, 'index']);
Route::post(
    '/telemetrias',
    [TelemetriaController::class, 'store']
);

Route::get(
    '/telemetrias/ultima',
    [TelemetriaController::class, 'ultima']
);

Route::get(
    '/telemetrias/historico',
    [TelemetriaController::class, 'historico']
);


/*
|--------------------------------------------------------------------------
| CONTROL DE LUCES (comandos) - pública, la consulta serial_bridge.py
|--------------------------------------------------------------------------
*/

Route::get(
    '/comandos/pendiente',
    [ComandoController::class, 'pendiente']
);


/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS CON JWT
|--------------------------------------------------------------------------
*/

Route::middleware('auth:api')
    ->group(function () {

        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::post('/comandos', [ComandoController::class, 'store']);

        Route::get('/incidentes/ultimo', [IncidenteFuegoController::class, 'ultimo']);

        Route::get('/configuraciones/{clave}', [ConfiguracionController::class, 'show']);
        Route::post('/configuraciones', [ConfiguracionController::class, 'update']);

        Route::get('/usuarios', [UserController::class, 'index']);
        Route::patch('/usuarios/{id}/role', [UserController::class, 'updateRole']);
        Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
        Route::patch('/actuadores', [ActuadorController::class, 'update']);

        Route::get('/bitacoras/exportar', [BitacoraController::class, 'exportarPdf']);

    });