<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificacionUsuario;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Obtener las últimas notificaciones y el conteo de no leídas para un usuario.
     */
    public function getPorUsuario(Request $request, $userId = null)
    {
        // Si hay usuario autenticado se usa auth()->id(), de lo contrario se toma de la URL o del Request
        $idUsuario = auth()->id() ?? $userId ?? $request->query('user_id');

        if (! $idUsuario) {
            return response()->json([
                'status' => false,
                'message' => 'Se requiere el ID del usuario.',
            ], 400);
        }

        // 1. Obtener las últimas 10 notificaciones con sus relaciones
        $notificaciones = NotificacionUsuario::with(['notificacion', 'user'])
            ->where('user_id', $idUsuario)
            ->latest()
            ->take(10)
            ->get();

        // 2. Obtener la cantidad de notificaciones sin leer
        $cantidadSinLeer = NotificacionUsuario::where('user_id', $idUsuario)
            ->whereNull('leido_at')
            ->count();

        return response()->json([
            'status' => true,
            'cantidad_no_leidas' => $cantidadSinLeer,
            'data' => $notificaciones,
        ], 200);
    }

    /**
     * Marcar una notificación específica como leída.
     */
    public function marcarComoLeida($id)
    {
        $notificacion = NotificacionUsuario::find($id);

        if (! $notificacion) {
            return response()->json([
                'status' => false,
                'message' => 'Notificación no encontrada.',
            ], 404);
        }

        $notificacion->update([
            'leido_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Notificación marcada como leída.',
            'data' => $notificacion,
        ], 200);
    }

    /**
     * Marcar todas las notificaciones de un usuario como leídas.
     */
    public function marcarTodasComoLeidas($userId)
    {
        NotificacionUsuario::where('user_id', $userId)
            ->whereNull('leido_at')
            ->update([
                'leido_at' => now(),
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Todas las notificaciones han sido marcadas como leídas.',
        ], 200);
    }

    /**
     * Obtener las últimas notificaciones y el conteo de no leídas para un usuario.
     */
    public function getCantidadSinLeerPorUsuario(Request $request, $userId = null)
    {
        // Si hay usuario autenticado se usa auth()->id(), de lo contrario se toma de la URL o del Request
        $idUsuario = auth()->id() ?? $userId ?? $request->query('user_id');

        if (! $idUsuario) {
            return response()->json([
                'status' => false,
                'message' => 'Se requiere el ID del usuario.',
            ], 400);
        }

        // 2. Obtener la cantidad de notificaciones sin leer
        $cantidadSinLeer = NotificacionUsuario::where('user_id', $idUsuario)
            ->whereNull('leido_at')
            ->count();

        return response()->json([
            'status' => true,
            'cantidad_no_leidas' => $cantidadSinLeer,
        ], 200);
    }
}
