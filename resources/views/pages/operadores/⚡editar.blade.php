<?php

use Livewire\Component;
use App\Models\Operador;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $modalAbierto = false;

    public $id;
    public $codigo_operador = '';
    public $nombre_operador = '';
    public $telefono_operador = 'N/A';
    public $estado_operador = true;
    public $operador;

    public function mount($id)
    {
        $this->id = $id;
    }

    protected function rules()
    {
        return [
            'nombre_operador' => 'required|string|max:255',
            'telefono_operador' => 'nullable|string|max:255',
            'estado_operador' => 'boolean',
        ];
    }

    public function abrirModal()
    {
        $this->resetFormulario();
        $this->operador = Operador::findOrFail($this->id);
        $this->nombre_operador = $this->operador->nombre_operador;
        $this->codigo_operador = $this->operador->codigo_operador;
        $this->telefono_operador = $this->operador->telefono_operador;
        $this->estado_operador = $this->operador->estado_operador;
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
            $this->operador->update([
                'nombre_operador' => $this->nombre_operador,
                'telefono_operador' => $this->telefono_operador,
                'estado_operador' => $this->estado_operador,
            ]);

            DB::commit();
            $this->dispatch('operador-actualizado');
            $this->dispatch('notificar', type: 'success', title: 'Éxito:', message: 'Operador editado correctamente.');
            $this->cerrarModal();
        } catch (QueryException $e) {
            // Código 1062 = entrada duplicada en MySQL
            DB::rollBack();

            if ($e->errorInfo[1] == 1062) {
                $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Ya existe un equipo con ese nombre.', autoClose: false);
            } else {
                $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Ocurrió un problema al guardar. Intenta nuevamente.', autoClose: false);
            }

            // Aquí conviene loguear el error real para revisarlo después
            \Log::error('Error al crear el operador: ' . $e->getMessage());
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Ocurrió un error inesperado.', autoClose: false);

            \Log::error('Error inesperado al crear el operador: ' . $e->getMessage());
        }
    }

    private function resetFormulario()
    {
        $this->reset(['nombre_operador', 'telefono_operador']);
        $this->estado_operador = true;
        $this->resetValidation();
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
            <h3 class="text-lg font-semibold">Editar Operador</h3>
            <button type="button" wire:click="cerrarModal" class="btn btn-text btn-circle btn-sm" aria-label="Close">
                <span class="icon-[tabler--x] size-4"></span>
            </button>
        </div>
        <div class="p-4">
            <div class="text-left font-semibold mb-4">
                <label class="label-text" for="nombre_operador">Nombre Operador</label>
                <input type="text" wire:model="nombre_operador" id="nombre_operador" class="input"
                    placeholder="Ingrese el nombre del operador">
                @error('nombre_operador')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="text-left font-semibold mb-4">
                <label class="label-text" for="">Télefono</label>
                <input type="text" wire:model="telefono_operador" id="telefono_operador" class="input"
                    placeholder="Número de télefono">
                @error('telefono_operador')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="estado_operador" id="estado_operador" class="checkbox">
                <label class="label-text" for="estado_operador">Activo</label>
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
