<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistribucionMaquinaria;
use Illuminate\Http\Request;
use App\Services\NotificacionService;

class DistribucionController extends Controller
{
    /**
     * Obtener las distribuciones asignadas a un operador.
     * Si no se envía operador_id, se pueden consultar todas o filtrar por parámetro.
     */
    public function getPorOperador(Request $request, $operadorId)
    {
        $distribuciones = DistribucionMaquinaria::with([
            'equipo:id,inventario,nombre_equipo', // Ajusta los campos según la tabla equipos
            'implemento:id,inventario,nombre_implemento', // Ajusta según la tabla implementos
            'solicitud'
        ])
            ->where('operador_id', $operadorId)
            ->whereIn('estado', ['Pendiente', 'En Proceso']) // Filtra solo activas
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $distribuciones,
        ], 200);
    }

    /**
     * Obtener el detalle de una asignación específica.
     */
    public function show($id)
    {
        $distribucion = DistribucionMaquinaria::with([
            'equipo',
            'implemento',
            'solicitud',
            'operador',
            'tiempoPerdidoDetalle'
        ])
            ->find($id);

        if (!$distribucion) {
            return response()->json([
                'status' => false,
                'message' => 'Distribución no encontrada',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $distribucion,
        ], 200);
    }

    public function updateHorometro(Request $request, $id)
    {
        $distribucion = DistribucionMaquinaria::find($id);

        if (!$distribucion) {
            return response()->json([
                'status' => false,
                'message' => 'Distribución no encontrada.',
            ], 404);
        }

        // Validación de campos
        $request->validate([
            'horometro_inicial' => 'nullable|numeric|gte:0',
            'horometro_final'   => 'nullable|numeric|gte:0',
            'hora_inicio'        => 'nullable|date_format:H:i,H:i:s',
            'hora_fin'           => 'nullable|date_format:H:i,H:i:s',
            'observacion'        => 'nullable|string|max:255',
            'estado'             => 'nullable|string|in:Pendiente,En Proceso,Completada',
        ]);

        // Validación de lógica de negocio: Horómetro final no puede ser menor al inicial
        $horometroInicial = $request->filled('horometro_inicial')
            ? $request->horometro_inicial
            : $distribucion->horometro_inicial;

        if ($request->filled('horometro_final') && $horometroInicial !== null) {
            if ($request->horometro_final <= $horometroInicial) {
                return response()->json([
                    'status' => false,
                    'message' => 'El horómetro final debe ser mayor que el horómetro inicial (' . $horometroInicial . ').',
                ], 422);
            }
        }

        // Determinar automáticamente el estado si no se envía explícitamente
        $nuevoEstado = $request->estado ?? $distribucion->estado;
        if ($request->filled('horometro_final') && $request->filled('hora_fin')) {
            $nuevoEstado = 'Completada';
        } elseif ($request->filled('horometro_inicial') && $nuevoEstado === 'Pendiente') {
            $nuevoEstado = 'En Proceso';
        }

        // Actualizar solo los campos enviados
        $distribucion->update([
            'horometro_inicial' => $request->horometro_inicial ?? $distribucion->horometro_inicial,
            'horometro_final'   => $request->horometro_final ?? $distribucion->horometro_final,
            'hora_inicio'        => $request->hora_inicio ?? $distribucion->hora_inicio,
            'hora_fin'           => $request->hora_fin ?? $distribucion->hora_fin,
            'observacion'        => $request->observacion ?? $distribucion->observacion,
            'estado'             => $nuevoEstado,
        ]);

        if($distribucion->estado === 'Completada') {
            NotificacionService::enviar(
            [
                'titulo' => 'Labor Finalizada',
                'mensaje' => "Se ha finalizado la labor {$distribucion->observacion} asignada para la fecha {$distribucion->fecha}.",
                'tipo_destinatario' => 'rol',
                'rol_destino'       => 'Operador|Administrador|Supervisor',
                'url' => route('distribuciones-diarias'),
                'notificable_type' => DistribucionMaquinaria::class,
                'notificable_id' => $distribucion->id,
            ],
            $distribucion->operador_id,
        );
        }else{
            NotificacionService::enviar(
            [
                'titulo' => 'Labor En Proceso',
                'mensaje' => "Se ha iniciado la labor {$distribucion->observacion} asignada al operador {$distribucion->operador->nombre_operador} para la fecha {$distribucion->fecha}.",
                'tipo_destinatario' => 'rol',
                'rol_destino'       => 'Operador|Administrador|Supervisor',
                'url' => route('distribuciones-diarias'),
                'notificable_type' => DistribucionMaquinaria::class,
                'notificable_id' => $distribucion->id,
            ],
            $distribucion->operador_id,
        );
        }

        return response()->json([
            'status' => true,
            'message' => 'Horómetro y horas actualizados correctamente.',
            'data' => $distribucion->fresh(['equipo', 'implemento', 'solicitud']),
        ], 200);
    }

    public function updateEstado(Request $request, $id)
    {
        $distribucion = DistribucionMaquinaria::find($id);

        if (!$distribucion) {
            return response()->json([
                'status' => false,
                'message' => 'Distribución no encontrada.',
            ], 404);
        }

        // Validación del nuevo estado
        $request->validate([
            'estado' => 'required|string|in:Pendiente,En Proceso,Detenido,Completado,Cancelado',
        ]);

        // Actualizar solo el campo estado
        $distribucion->update([
            'estado' => $request->estado,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Estado actualizado a ' . $request->estado . ' correctamente.',
            'data' => [
                'id' => $distribucion->id,
                'estado' => $distribucion->estado,
            ],
        ], 200);
    }
}
