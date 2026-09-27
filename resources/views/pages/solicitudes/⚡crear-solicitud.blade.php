<?php

use Livewire\Component;
use App\Models\Actividad;
use App\Models\TipoEquipo;
use App\Models\Solicitud;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificacionService;

new class extends Component {
    public int $actividad_id = 0;
    public string $tipo_equipo = '';
    public string $descripcion = '';
    public string $sector = '';
    public $fecha_solicitud;

    public function with(): array
    {
        return [
            'actividades' => Actividad::all(),
            'tipo_equipos' => TipoEquipo::all(),
        ];
    }

    protected function rules(): array
    {
        return [
            'actividad_id' => 'required|exists:actividades,id',
            'fecha_solicitud' => 'required|date|after_or_equal:today',
            'descripcion' => 'required|string|max:255',
            'sector' => 'required|string|max:255',
            'tipo_equipo' => 'required|string|max:255',
        ];
    }

    // Mensajes de error personalizados (Opcional)
    protected function messages(): array
    {
        return [
            'actividad_id.required' => 'Debe seleccionar una actividad.',
            'actividad_id.exists' => 'La actividad seleccionada no es válida.',
            'fecha_requerida.required' => 'La fecha requerida es obligatoria.',
            'fecha_requerida.after_or_equal' => 'La fecha debe ser igual o posterior a hoy.',
            'tipo_equipo.required' => 'Debe seleccionar una tipo de equipo.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'sector.required' => 'La descripción es obligatoria.',
        ];
    }

    // 3. Método para Guardar el Registro
    public function crearSolicitud()
    {
        // A. Ejecutar Validaciones de Formulario
        $validatedData = $this->validate();

        $this->errorMessage = '';

        try {
            // B. Transacción de Base de Datos para Integridad
            DB::beginTransaction();

            $solicitud = Solicitud::create([
                'actividad_id' => $validatedData['actividad_id'],
                'solicitante' => Auth::id(), // Usuario autenticado actual
                'fecha_solicitud' => $validatedData['fecha_solicitud'],
                'descripcion' => $validatedData['descripcion'],
                'tipo_equipo' => $validatedData['tipo_equipo'],
                'sector' => $validatedData['sector'],
                'estado' => 'Pendiente',
            ]);

            NotificacionService::enviar(
                [
                    'titulo' => 'Solicitud Creada',
                    'mensaje' => "Se ha creado una nueva solicitud con código #{$solicitud->codigo_solicitud} esperando a ser revisada.",
                    'tipo_destinatario' => 'rol',
                    'rol_destino'       => 'Administrador|Supervisor',
                    'url' => route('solicitudes'),
                    'notificable_type' => Solicitud::class,
                    'notificable_id' => $solicitud->id,
                ],
                $solicitud->usuarioSolicitante->id,
            );

            DB::commit();

            // C. Limpiar Formulario o Redireccionar con Mensaje Flash
            session()->flash('success', 'La solicitud de maquinaria se ha registrado correctamente.');
            $this->dispatch('notificar', type: 'success', title: 'Éxito:', message: 'La solicitud de maquinaría se ha registrado correctamente.');

            return redirect()->route('mis-solicitudes');
        } catch (\Exception $e) {
            // D. Control de Errores e Inconsistencias
            DB::rollBack();

            // Registrar el error detallado en logs de Laravel
            Log::error('Error guardando solicitud: ' . $e->getMessage(), [
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
        <h3 class="text-lg font-semibold">Crear Nueva Solicitud</h3>
    </div>

    <form wire:submit.prevent="crearSolicitud" class="mt-6">
        <div class="grid grid-cols-3 mt-4 pl-5 pr-5 w-full gap-2">
            <div class="w-auto col-span-1">
                <label class="label-text" for="defaultInput">Actividad</label>
                <div class="flex gap-2">
                    <select wire:model.live="actividad_id" class="select max-w-sm appearance-none" aria-label="select">
                        <option value="">Seleccione una Actividad</option>
                        @foreach ($actividades as $item)
                            <option value="{{ $item->id }}">{{ $item->codigo_actividad }} -
                                {{ $item->nombre_actividad }}</option>
                        @endforeach
                    </select>
                </div>
                @error('actividad_id')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>
            <div class="w-auto col-span-1">
                <label class="label-text" for="defaultInput">Equipo Solicitado</label>
                <div class="flex gap-2">
                    <select wire:model.live="tipo_equipo" class="select max-w-sm appearance-none" aria-label="select">
                        <option value="">Seleccione el tipo de equipo</option>
                        @foreach ($tipo_equipos as $item)
                            <option value="{{ $item->nombre_tipo_equipo }}">{{ $item->nombre_tipo_equipo }}</option>
                        @endforeach
                    </select>

                </div>
                @error('tipo_equipo')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="w-auto col-span-1">
                <label class="label-text" for="defaultInput">Fecha Solicitado</label>
                <div class="flex gap-2">
                    <div class="relative w-full">
                        <input wire:model="fecha_solicitud" type="date" class="input" id="defaultInput" />
                        @error('fecha_solicitud')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

            </div>
        </div>

        <div class="grid grid-cols-2 mt-4 pl-5 pr-5 w-full gap-4">
            <div class="w-full col-span-1">
                <div class="">
                    <label class="label-text" for="textareaLabel">Sector</label>
                    <textarea wire:model="sector" class="textarea" placeholder="Describa el sector" id="textareaLabel"></textarea>
                    @error('sector')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            <div class="w-full col-span-1">
                <div class="">
                    <label class="label-text" for="textareaLabel">Descripción</label>
                    <textarea wire:model="descripcion" class="textarea" placeholder="Escriba la descrición" id="textareaLabel"></textarea>
                    @error('descripcion')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
        <div class="card-actions justify-end mt-6 gap-2">
            <a href="{{ route('mis-solicitudes') }}"class="btn btn-secondary">Cancelar</a>
            </button>
            <button class="btn btn-accent" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>Guardar Solicitud</span>
                <span wire:loading class="loading loading-spinner">Guardando...</span>
            </button>
        </div>
    </form>



</div>
