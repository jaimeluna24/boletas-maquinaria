<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DistribucionController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\TiempoPerdidoController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


// Ruta pública para la app móvil
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas para la app móvil
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Obtener todas las asignaciones de un operador específico (ej: /api/operador/1/distribuciones)
Route::get('/operador/{operadorId}/distribuciones', [DistribucionController::class, 'getPorOperador']);

// Obtener el detalle de una distribución
Route::get('/distribuciones/{id}', [DistribucionController::class, 'show']);
Route::put('/distribuciones/{id}/horometro', [DistribucionController::class, 'updateHorometro']);
Route::patch('/distribuciones/{id}/estado', [DistribucionController::class, 'updateEstado']);


// Obtener notificaciones de un usuario
Route::get('/usuarios/{userId}/notificaciones', [NotificacionController::class, 'getPorUsuario']);

// Marcar una notificación individual como leída
Route::patch('/notificaciones/{id}/leer', [NotificacionController::class, 'marcarComoLeida']);

// Marcar todas las notificaciones del usuario como leídas
Route::patch('/usuarios/{userId}/notificaciones/leer-todas', [NotificacionController::class, 'marcarTodasComoLeidas']);

// Obtener cantidad de notificaciones de un usuario din leer
Route::get('/usuarios/{userId}/notificaciones-cantidad', [NotificacionController::class, 'getCantidadSinLeerPorUsuario']);

Route::post('/tiempo-perdido/crear', [TiempoPerdidoController::class, 'store']);
Route::get('/tiempo-perdido/{distribucionId}/registros', [TiempoPerdidoController::class, 'getPorDistribucion']);


