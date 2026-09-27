<?php

use Livewire\Component;
use App\Models\Actividad;
use App\Models\TipoEquipo;
use App\Models\Solicitud;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public int $actividad_id = 0;
    public string $tipo_equipo = '';
    public string $descripcion = '';
    public string $sector = '';
    public $fecha_solicitud;

    public $solicitud;
    public $solicitud_id = 0;

    public function mount($id)
    {
        $this->solicitud = Solicitud::find($id);
        $this->solicitud_id = $this->solicitud->id;
        $this->actividad_id = $this->solicitud->actividad_id;
        $this->tipo_equipo = $this->solicitud->tipo_equipo;
        $this->descripcion = $this->solicitud->descripcion;
        $this->sector = $this->solicitud->sector;
        $this->fecha_solicitud = $this->solicitud->fecha_solicitud;
    }

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
    public function actualizarSolicitud()
    {
        // Ejecutar Validaciones de Formulario
        $validatedData = $this->validate();

        $this->errorMessage = '';

        // Iniciar Transacción de Base de Datos
        DB::beginTransaction();

        try {
            $this->solicitud->actividad_id = $validatedData['actividad_id'];
            $this->solicitud->fecha_solicitud = $validatedData['fecha_solicitud'];
            $this->solicitud->descripcion = $validatedData['descripcion'];
            $this->solicitud->sector = $validatedData['sector'];
            $this->solicitud->tipo_equipo = $validatedData['tipo_equipo'];

            $this->solicitud->save();

            DB::commit();

            // Limpiar Formulario o Redireccionar con Mensaje Flash
            session()->flash('success', 'La solicitud de maquinaria se ha registrado correctamente.');
            $this->dispatch('notificar', type: 'success', title: 'Éxito:', message: 'La solicitud de maquinaría se ha actualizado correctamente.');

            // return redirect()->route('mis-solicitudes');
        } catch (\Exception $e) {
            // Control de Errores e Inconsistencias
            DB::rollBack();

            // Registrar el error detallado en logs de Laravel
            Log::error('Error actualizando solicitud: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'data' => $validatedData,
            ]);

            // Asignar mensaje visible para el usuario
            $this->errorMessage = 'Ocurrió un error inesperado al procesar la solicitud. Por favor, inténtelo de nuevo.';
            $this->dispatch('notificar', type: 'error', title: 'Error:', message: $e->getMessage());
        }
    }

    public function eliminarSolicitud()
    {
        DB::beginTransaction();

        try {
            // 1. Buscar el registro
            $solicitud = Solicitud::findOrFail($this->solicitud_id);

            // 2. Ejecutar el Soft Delete
            $solicitud->delete(); // Esto llena automáticamente la columna 'deleted_at'

            DB::commit();

            // 3. Respuesta en caso de éxito (para redirección con sesión flash)
            return redirect()->route('mis-solicitudes')->with('success', 'La solicitud fue eliminada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Registrar el error detallado en los logs
            Log::error('Error al realizar soft delete en la solicitud: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'solicitud_id' => $id,
            ]);

            return redirect()->back()->with('error', 'Ocurrió un error inesperado al intentar eliminar el registro.');
        }
    }
};
?>

<div class="p-10">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold">Editar Solicitud: {{ $solicitud->codigo_solicitud }}</h3>
    </div>

    <form wire:submit.prevent="actualizarSolicitud" class="mt-6">
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
            <button class="btn btn-accent" type="submit" wire:loading.attr="disabled"
                wire:target="actualizarSolicitud">
                <span wire:target="actualizarSolicitud" wire:loading.remove>Actualizar Solicitud</span>
                <span wire:target="actualizarSolicitud" wire:loading
                    class="loading loading-spinner">Actualizando...</span>
            </button>
            <button type="button" class="btn btn-error" aria-haspopup="dialog" aria-expanded="false"
                aria-controls="slide-down-animated-modal" data-overlay="#slide-down-animated-modal">
            <span wire:target="eliminarSolicitud" class="icon-[mdi--trash-outline] size-5"
                            wire:loading.remove></span>
                        <span wire:target="eliminarSolicitud" wire:loading
                            class="loading loading-spinner">Eliminando...</span>
            </button>

        </div>
    </form>






    <div id="slide-down-animated-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 hidden"
        role="dialog" tabindex="-1">
        <div class="modal-dialog overlay-open:mt-12 overlay-open:duration-300 transition-all ease-out">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Eliminar Solicitud</h3>
                    <button type="button" class="btn btn-text btn-circle btn-sm absolute top-3"
                        aria-label="Close" data-overlay="#slide-down-animated-modal">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>
                <div class="modal-body">
                    Se eliminara la solicitud {{ $solicitud->codigo_solicitud }} permanentemente
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft btn-secondary" data-overlay="#slide-down-animated-modal">
                        Cerrar
                    </button>
                    <button wire:click="eliminarSolicitud()" wire:target="eliminarSolicitud" class="btn btn-error"
                        type="button" wire:loading.attr="disabled">
                        <span wire:target="eliminarSolicitud"
                            wire:loading.remove>Confirmar</span>
                        <span wire:target="eliminarSolicitud" wire:loading
                            class="loading loading-spinner">Eliminando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>



</div>
