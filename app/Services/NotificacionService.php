<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
    /**
     * Envía una notificación a uno, varios usuarios o un rol.
     *
     * @param  array<string, mixed>  $datos  [titulo, mensaje, tipo_destinatario, rol_destino, url, notificable_type, notificable_id]
     * @param  int|array<int>|null  $destinatarios  ID de usuario o array de IDs (si tipo_destinatario es 'usuario')
     */
    public static function enviar(array $datos, int|array|null $destinatarios = null): bool
    {
        DB::beginTransaction();

        try {
            // 1. Crear el registro principal de la notificación
            $notificacion = Notificacion::create([
                'titulo' => $datos['titulo'],
                'mensaje' => $datos['mensaje'],
                'tipo_destinatario' => $datos['tipo_destinatario'], // 'usuario', 'rol', 'todos'
                'rol_destino' => $datos['rol_destino'] ?? null,
                'url' => $datos['url'] ?? null,
                'notificable_type' => $datos['notificable_type'] ?? null,
                'notificable_id' => $datos['notificable_id'] ?? null,
                'creado_por' => auth()->id(),
            ]);

            // 2. Resolver los IDs de los usuarios según el tipo de destinatario
            $userIds = self::obtenerUserIds($datos['tipo_destinatario'], $destinatarios, $datos['rol_destino'] ?? null);

            if (! empty($userIds)) {
                // 3. Vincular a la tabla pivote sin duplicados
                $notificacion->usuarios()->attach(array_unique($userIds));
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error enviando notificación: '.$e->getMessage(), [
                'datos' => $datos,
                'destinatarios' => $destinatarios,
            ]);

            return false;
        }
    }

    /**
     * Marca una notificación como leída para un usuario específico.
     */
    public static function marcarComoLeida(int $notificacionId, ?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        return DB::table('notificacion_usuarios')
            ->where('notificacion_id', $notificacionId)
            ->where('user_id', $userId)
            ->update(['leido_at' => now()]) > 0;
    }

    /**
     * Marca TODAS las notificaciones pendientes como leídas para un usuario.
     */
    public static function marcarTodasComoLeidas(?int $userId = null): int
    {
        $userId = $userId ?? auth()->id();

        return DB::table('notificacion_usuarios')
            ->where('user_id', $userId)
            ->whereNull('leido_at')
            ->update(['leido_at' => now()]);
    }

    /**
     * Obtiene el listado de IDs de usuario según el alcance especificado.
     *
     * @param  int|array<int>|null  $destinatarios
     * @param  string|array<string>|null  $rolDestino
     * @return array<int>
     */
    private static function obtenerUserIds(string $tipo, int|array|null $destinatarios = null, string|array|null $rolDestino = null): array
    {
        // Si viene una cadena con '|', la convertimos en array: ['Administrador', 'Supervisor']
        if ($tipo === 'rol' && is_string($rolDestino)) {
            $rolDestino = str_contains($rolDestino, '|')
                ? array_map('trim', explode('|', $rolDestino))
                : trim($rolDestino);
        }

        return match ($tipo) {
            'usuario' => is_array($destinatarios) ? $destinatarios : array_filter([$destinatarios]),
            'rol' => ! empty($rolDestino) ? User::role($rolDestino)->pluck('id')->toArray() : [],
            'todos' => User::pluck('id')->toArray(),
            default => [],
        };
    }
}
