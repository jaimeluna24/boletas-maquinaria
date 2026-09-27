<?php

use Livewire\Component;
use App\Models\Implemento;
use App\Models\TipoImplemento;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $modalAbierto = false;

    public $id;
    public $inventario = '';
    public $nombre_implemento = '';
    public $activo = true;
    public $tipo_implemento_id = '';
    public $implemento;

    public function mount($id)
    {
        $this->id = $id;
    }

    protected function rules()
    {
        return [
            'nombre_implemento' => 'required|string|max:255',
            'inventario' => 'required|string|max:255',
            'tipo_implemento_id' => 'required|exists:tipo_equipos,id',
            'activo' => 'boolean',
        ];
    }

    public function abrirModal()
    {
        $this->resetFormulario();
        $this->implemento = Implemento::findOrFail($this->id);
        $this->inventario = $this->implemento->inventario;
        $this->nombre_implemento = $this->implemento->nombre_implemento;
        $this->activo = $this->implemento->activo;
        $this->tipo_implemento_id = $this->implemento->tipo_implemento_id;
        $this->modalAbierto = true;
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetFormulario();
    }

    public function guardar()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $this->implemento->update([
                'nombre_implemento' => $this->nombre_implemento,
                'inventario' => $this->inventario,
                'activo' => $this->activo,
                'tipo_implemento_id' => $this->tipo_implemento_id,
            ]);

            DB::commit();
            $this->dispatch('implemento-actualizado');
            $this->dispatch('notificar', type: 'success', title: 'Éxito:', message: 'Implemento creado correctamente.');
            $this->cerrarModal();
        } catch (QueryException $e) {
            // Código 1062 = entrada duplicada en MySQL
            DB::rollBack();

            if ($e->errorInfo[1] == 1062) {
                $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Ya existe un implemento con ese nombre.', autoClose: false);
            } else {
                $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Ocurrió un problema al guardar. Intenta nuevamente.', autoClose: false);
            }

            // Aquí conviene loguear el error real para revisarlo después
            \Log::error('Error al crear el implemento: ' . $e->getMessage());
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Ocurrió un error inesperado.', autoClose: false);

            \Log::error('Error inesperado al crear el implemento: ' . $e->getMessage());
        }
    }

    private function resetFormulario()
    {
        $this->reset(['nombre_implemento', 'inventario', 'tipo_implemento_id']);
        $this->activo = true;
        $this->resetValidation();
    }

    public function with(): array
    {
        return [
            'tipo_implementos' => TipoImplemento::all(),
        ];
    }
};
?>

<div>
    <div class="text-center">
        <button wire:click="abrirModal" class="btn btn-circle btn-text btn-sm text-yellow-500">
            <span class="icon-[tabler--pencil] size-5"></span>
        </button>
    </div>


    <x-modal :show="$modalAbierto" max-width="md">
        <div class="flex justify-between items-center p-4 border-b border-base-content/10">
            <h3 class="text-lg font-semibold">Editar Implemento</h3>
            <button type="button" wire:click="cerrarModal" class="btn btn-text btn-circle btn-sm" aria-label="Close">
                <span class="icon-[tabler--x] size-4"></span>
            </button>
        </div>

        <div class="p-4">
            <div class="text-left font-semibold mb-4">
                <label class="label-text" for="inventario">Inventario</label>
                <input type="text" wire:model="inventario" id="inventario" class="input"
                    placeholder="Ingrese el Inventario">
                @error('inventario')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="text-left font-semibold mb-4">
                <label class="label-text" for="">Nombre Implemento</label>
                <input type="text" wire:model="nombre_implemento" id="nombre_implemento" class="input"
                    placeholder="Nombre del Implemento">
                @error('nombre_implemento')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="text-left mb-4">
                <label class="label-text  font-semibold" for="tipo_implemento_id">Tipo Implemento</label>
                <select wire:model="tipo_implemento_id" id="tipo_implemento_id" class="select">
                    <option value="">Seleccione el Tipo de Implemento</option>
                    @foreach ($tipo_implementos as $item)
                        <option value="{{ $item->id }}">{{ $item->nombre_tipo_implemento }}</option>
                    @endforeach
                </select>
                @error('tipo_implemento_id')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="activo" id="activo" class="checkbox">
                <label class="label-text" for="activo">Activo</label>
            </div>
        </div>

        <div class="flex justify-end gap-2 p-4 border-t border-base-content/10">
            <button type="button" wire:click="cerrarModal" class="btn btn-default">
                Cancelar
            </button>
            <button type="button" wire:click="guardar" class="btn btn-primary">
                Guardar
            </button>
        </div>
    </x-modal>
</div>
