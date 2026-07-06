<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TelemetriaController;
use Illuminate\Support\Facades\Route;


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


/*
|--------------------------------------------------------------------------
| TELEMETRÍA
|--------------------------------------------------------------------------
|
| Estas rutas sirven para:
|
| POST histórico físico:
| ESP32 -> USB -> Python -> Laravel
|
| GET:
| React -> Laravel -> MySQL
|
*/

// Guardar lectura nueva
Route::post(
    '/telemetrias',
    [TelemetriaController::class, 'store']
);

// Obtener última lectura
Route::get(
    '/telemetrias/ultima',
    [TelemetriaController::class, 'ultima']
);

// Obtener histórico por fecha
Route::get(
    '/telemetrias/historico',
    [TelemetriaController::class, 'historico']
);


/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS CON JWT
|--------------------------------------------------------------------------
*/

Route::middleware('auth:api')
    ->group(function () {

        Route::get(
            '/me',
            [AuthController::class, 'me']
        );

        Route::post(
            '/logout',
            [AuthController::class, 'logout']
        );
    });