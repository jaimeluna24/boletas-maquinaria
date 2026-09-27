<?php

use Livewire\Component;
use App\Models\DistribucionMaquinaria;
use App\Models\Solicitud;
use App\Models\Actividad;
use App\Models\User;
use App\Models\Equipo;
use App\Models\Operador;
use App\Models\Implemento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificacionService;


new class extends Component {
    public int $operador_id = 0;
    public $implemento_id = 0;
    public int $equipo_id = 0;
    public $solicitud_id = 0;

    public $hora_inicio;
    public $fecha;
    public $horometro_inicio;
    public $observacion = '';
    public $descripcion = '';
    public $lugar = '';

    // public $descripcion;
    // public $sector;
    // public $solicitante;
    // public $nombre_actividad;

    public function with(): array
    {
        return [
            'operadores' => Operador::all(),
            'equipos' => Equipo::all(),
            'implementos' => Implemento::all(),
            // 'solicitudes' => Solicitud::where('estado', 'Aprobada')->get(),
        ];
    }

    protected function rules(): array
    {
        return [
            'operador_id' => 'required|exists:operadores,id',
            'equipo_id' => 'required|exists:equipos,id',
            // 'solicitud_id' => 'required|exists:solicitudes,id',
            'implemento_id' => 'nullable',
            'fecha' => 'required|date|after_or_equal:today',
            'observacion' => 'nullable|string|max:255',
            'descripcion' => 'required|string|max:255',
            'lugar' => 'required|string|max:255',
            'hora_inicio' => 'nullable|date_format:H:i',
        ];
    }

    // Mensajes de error personalizados (Opcional)
    protected function messages(): array
    {
        return [
            // Operador
            'operador_id.required' => 'Debe seleccionar un operador.',
            'operador_id.exists' => 'El operador seleccionado no es válido o no existe.',

            // Equipo
            'equipo_id.required' => 'Debe seleccionar un equipo.',
            'equipo_id.exists' => 'El equipo seleccionado no es válido o no existe.',

            // // Solicitud
            // 'solicitud_id.required' => 'La solicitud asociada es obligatoria.',
            // 'solicitud_id.exists' => 'La solicitud seleccionada no es válida.',

            // Fecha
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'Ingrese una fecha con un formato válido.',
            'fecha.after_or_equal' => 'La fecha debe ser igual o posterior al día de hoy.',

            // Observación
            'observacion.string' => 'La observación debe ser un texto válido.',
            'observacion.max' => 'La observación no puede exceder los 255 caracteres.',

            // Lugar
            'lugar.required' => 'El lugar o ubicación es obligatorio.',
            'lugar.string' => 'El lugar debe ser un texto válido.',
            'lugar.max' => 'El nombre del lugar no puede exceder los 255 caracteres.',

            'hora_inicio.date_format' => 'La hora de inicio debe tener un formato válido (HH:MM).',

            'descripcion.string' => 'La descripcion debe ser un texto válido.',
            'descripcion.max' => 'La descripcion no puede exceder los 255 caracteres.',
        ];
    }

    // public function updatedSolicitudId($value)
    // {
    //     if (empty($value)) {
    //         $this->reset(['nombre_actividad', 'solicitante', 'sector', 'descripcion']);
    //         return;
    //     }

    //     // Consulta a la base de datos trayendo la solicitud con sus relaciones si aplica
    //     $solicitud = Solicitud::with('actividad', 'usuarioSolicitante')->find($value);

    //     if ($solicitud) {
    //         // Cargas los datos de la solicitud en las propiedades públicas del formulario
    //         $this->nombre_actividad = $solicitud->actividad->nombre_actividad;
    //         $this->solicitante = $solicitud->usuarioSolicitante->nombre_completo;
    //         $this->sector = $solicitud->sector;
    //         $this->descripcion = $solicitud->descripcion;
    //     }
    // }

    // Método para Guardar el Registro
    public function crearDistribucion()
    {
        // Ejecutar Validaciones de Formulario
        $validatedData = $this->validate();

        $this->errorMessage = '';

        try {
            // Transacción de Base de Datos para Integridad
            DB::beginTransaction();

           $distribucion = DistribucionMaquinaria::create([
                'operador_id' => $validatedData['operador_id'],
                // 'solicitud_id' => $validatedData['solicitud_id'],
                'equipo_id' => $validatedData['equipo_id'],
                'implemento_id' => $validatedData['implemento_id'] ?? null,
                'creada_por' => Auth::id(), // Usuario autenticado actual
                'hora_inicio' => $validatedData['hora_inicio'] ?? null,
                'fecha' => $validatedData['fecha'],
                'observacion' => $validatedData['observacion'] ?? null,
                'descripcion' => $validatedData['descripcion'],
                'lugar' => $validatedData['lugar'],
                'estado' => 'Pendiente',
            ]);

             NotificacionService::enviar(
                [
                    'titulo' => 'Asignación de Labor',
                    'mensaje' => "Se ha te ha asignado la labor: {$distribucion->descripcion} para la fecha {$distribucion->fecha}.",
                    'tipo_destinatario' => 'rol',
                    'rol_destino'       => 'Operador',
                    'url' => route('distribuciones-diarias'),
                    'notificable_type' => DistribucionMaquinaria::class,
                    'notificable_id' => $distribucion->id,
                ],
                $distribucion->creada_por,
            );

            DB::commit();

            // Limpiar Formulario o Redireccionar con Mensaje Flash
            session()->flash('success', 'La distribucion de maquinaria se ha registrado correctamente.');
            $this->dispatch('notificar', type: 'success', title: 'Éxito:', message: 'La distribución de maquinaría se ha registrado correctamente.');

            return redirect()->route('distribuciones-diarias');
        } catch (\Exception $e) {
            // Control de Errores e Inconsistencias
            DB::rollBack();

            // Registrar el error detallado en logs de Laravel
            Log::error('Error guardando la distribucion: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'data' => $validatedData,
            ]);

            // Asignar mensaje visible para el usuario
            $this->errorMessage = 'Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo de nuevo.';
            $this->dispatch('notificar', type: 'error', title: 'Error:', message: $e->getMessage());
        }
    }
};
?>

<div class="p-10">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold">Crear Distribución</h3>
    </div>

    <form wire:submit.prevent="crearDistribucion" class="mt-6">
        <div class="grid grid-cols-6 mt-4 pl-5 pr-5 w-full gap-2">
            {{-- <div class="w-auto col-span-2">
                <label class="label-text" for="defaultInput">Solicitud</label>
                <div class="flex gap-2">
                    <select wire:model.live="solicitud_id" class="select max-w-sm appearance-none" aria-label="select">
                        <option value="">Seleccione la Solicitud</option>
                        @foreach ($solicitudes as $item)
                            <option value="{{ $item->id }}">{{ $item->codigo_solicitud }} -
                                {{ $item->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                @error('solicitud_id')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div> --}}
            {{-- <div class="w-auto col-span-2">
                <label class="label-text" for="defaultInput">Actividad</label>
                <div class="flex gap-2">
                    <div class="relative w-full">
                        <input disabled wire:model="nombre_actividad" type="text" class="input" id="defaultInput" />
                    </div>
                </div>
            </div>
            <div class="w-auto col-span-2">
                <label class="label-text" for="defaultInput">Solicitante</label>
                <div class="flex gap-2">
                    <div class="relative w-full">
                        <input disabled wire:model="solicitante" type="text" class="input" id="defaultInput" />
                    </div>
                </div>
            </div> --}}

            {{-- <div class="w-full col-span-3">
                <div>
                    <label class="label-text" for="textareaLabel">Sector</label>
                    <textarea disabled
                        class="textarea input w-full py-2 bg-base-200/50 text-base-content/60 border-base-content/20 disabled:bg-base-200/40 disabled:text-base-content/60 disabled:border-base-content/20"
                        placeholder="Describa el sector" id="textareaLabel">{{ trim($sector) }}</textarea>
                </div>
            </div>

            <div class="w-full col-span-3">
                <div>
                    <label class="label-text" for="textareaLabel">Descripción</label>
                    <textarea disabled
                        class="textarea input w-full py-2 bg-base-200/50 text-base-content/60 border-base-content/20 disabled:bg-base-200/40 disabled:text-base-content/60 disabled:border-base-content/20"
                        placeholder="Describa el sector" id="textareaLabel">{{ trim($descripcion) }}</textarea>
                </div>
            </div> --}}


            <div class="w-auto col-span-2">
                <label class="label-text" for="defaultInput">Operador</label>
                <div class="flex gap-2">
                    <select wire:model.live="operador_id" class="select max-w-sm appearance-none" aria-label="select">
                        <option value="">Seleccione el Operador</option>
                        @foreach ($operadores as $item)
                            <option value="{{ $item->id }}">{{ $item->nombre_operador }} - {{ $item->codigo_operador }}
                                </option>
                        @endforeach
                    </select>

                </div>
                @error('operador_id')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>
            <div class="w-auto col-span-2">
                <label class="label-text" for="defaultInput">Equipo</label>
                <div class="flex gap-2">
                    <select wire:model.live="equipo_id" class="select max-w-sm appearance-none" aria-label="select">
                        <option value="">Seleccione el Equipo</option>
                        @foreach ($equipos as $item)
                            <option value="{{ $item->id }}">{{ $item->inventario }} - {{ $item->nombre_equipo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('equipo_id')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>
            <div class="w-auto col-span-2">
                <label class="label-text" for="defaultInput">Implemento (Opcional)</label>
                <div class="flex gap-2">
                    <select wire:model.live="implemento_id" class="select max-w-sm appearance-none" aria-label="select">
                        <option value="0">Seleccione el Implemento</option>
                        @foreach ($implementos as $item)
                            <option value="{{ $item->id }}">{{ $item->inventario }} -
                                {{ $item->nombre_implemento }}</option>
                        @endforeach
                    </select>

                </div>
                @error('implemento_id')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>
            <div class="w-auto col-span-2">
                <label class="label-text" for="defaultInput">Fecha</label>
                <div class="flex gap-2">
                    <div class="relative w-full">
                        <input wire:model="fecha" type="date" class="input" id="defaultInput" />
                        @error('fecha')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="w-auto col-span-2">
                <label class="label-text" for="defaultInput">Hora Inicio (Opcional)</label>
                <div class="flex gap-2">
                    <div class="relative w-full">
                        <input wire:model="hora_inicio" type="time" class="input" id="defaultInput" />
                        @error('hora_inicio')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="w-auto col-span-2">
                <label class="label-text" for="defaultInput">Lugar</label>
                <div class="flex gap-2">
                    <div class="relative w-full">
                        <input wire:model="lugar" type="text" class="input" id="defaultInput" />
                        @error('lugar')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 mt-4 pl-5 pr-5 w-full gap-4">
            <div class="w-full col-span-1">
                <div class="">
                    <label class="label-text" for="textareaLabel">Descripción</label>
                    <textarea wire:model="descripcion" class="textarea" placeholder="Escriba la descrición" id="textareaLabel"></textarea>
                    @error('descripcion')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="w-full col-span-1">
                <div class="">
                    <label class="label-text" for="textareaLabel">Observacion</label>
                    <textarea wire:model="observacion" class="textarea" placeholder="Escriba la observacion" id="textareaLabel"></textarea>
                    @error('observacion')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>


        </div>
        <div class="card-actions justify-end mt-6 gap-2">
            <a href="{{ route('distribuciones-diarias') }}"class="btn btn-secondary">Cancelar</a>
            </button>
            <button class="btn btn-accent" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>Guardar Distribución</span>
                <span wire:loading class="loading loading-spinner">Guardando...</span>
            </button>
        </div>
    </form>



</div>
