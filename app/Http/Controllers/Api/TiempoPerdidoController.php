<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TiempoPerdidoDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TiempoPerdidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'nombre_tiempo_perdido'      => 'required|string|max:255',
            'distribucion_maquinaria_id' => 'required|integer',
            'fecha'                      => 'required|date',
            'hora_inicio'                => 'required|date_format:H:i',
            'hora_fin'                   => 'required|date_format:H:i',
            'observacion'                => 'nullable|string',
        ], [
            'hora_inicio.date_format' => 'El formato de hora inicial debe ser HH:mm.',
            'hora_fin.date_format' => 'El formato de hora final debe ser HH:mm.',
        ]);

         $validatorFecha = Validator::make($request->all(), [
            'hora_fin'                   => 'nullable|date_format:H:i|after:hora_inicio',
        ],
         [
            'hora_fin.after' => 'La hora final no puede ser menor o igual a la hora inicial.'
        ]);

        // 2. Si falla la validación, retornar respuesta 422
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Error de validación',
                'errors'  => $validator->errors()
            ], 422);
        }

         if ($validatorFecha->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'La hora final no debe ser menor a la inicial',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 3. Procesar el registro en la base de datos
        DB::beginTransaction();

        try {
            $detalle = TiempoPerdidoDetalle::create($validator->validated());

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Tiempo perdido registrado correctamente.',
                'data'    => $detalle
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $th->getMessage(),
                'data'    => $request->all()
            ], 500);
        }
    }

    public function getPorDistribucion(Request $request, $distribucionId = null)
    {
        // Si hay usuario autenticado se usa auth()->id(), de lo contrario se toma de la URL o del Request

        if (!$distribucionId) {
            return response()->json([
                'status' => false,
                'message' => 'Se requiere el ID de la distribución.',
            ], 400);
        }

        // 1. Obtener las últimas 10 notificaciones con sus relaciones
        $tiempoPerdido = TiempoPerdidoDetalle::with(['distribucionMaquinaria'])
            ->where('distribucion_maquinaria_id', $distribucionId)
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $tiempoPerdido,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(TiempoPerdidoDetalle $tiempoPerdidoDetalle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TiempoPerdidoDetalle $tiempoPerdidoDetalle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TiempoPerdidoDetalle $tiempoPerdidoDetalle)
    {
        //
    }
}
