<?php

use Livewire\Component;
use App\Models\Operador;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

new class extends Component {
    public $modalAbierto = false;

    public $nombre_operador = '';
    public $telefono_operador = '';
    public $estado_operador = true;

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
            Operador::create([
                'nombre_operador' => $this->nombre_operador,
                'telefono_operador' => $this->telefono_operador,
                'estado_operador' => $this->estado_operador
            ]);
            DB::commit();

            $this->dispatch('notificar', type: 'success', title: 'Éxito:', message: 'Operador creado correctamente.');

            $this->cerrarModal();
            $this->dispatch('operador-actualizado');
        } catch (QueryException $e) {
            DB::rollBack();

            // Código 1062 = entrada duplicada en MySQL
            if ($e->errorInfo[1] == 1062) {
                $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Ya existe un operador con ese codigo.', autoClose: false);
            } else {
                $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Ocurrió un problema al guardar. Intenta nuevamente.', autoClose: false);
            dd($e->getMessage());

                }

            // Aquí conviene loguear el error real para revisarlo después
            \Log::error('Error al crear el operador: ' . $e->getMessage());
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e->getMessage());
            $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Ocurrió un error inesperado.', autoClose: false);

            \Log::error('Error inesperado al crear el operador: ' . $e->getMessage());
        }
    }

    private function resetFormulario()
    {
        $this->reset(['nombre_operador', 'telefono_operador']);
        $this->activo = true;
        $this->resetValidation();
    }
};
?>

<div>
    <button wire:click="abrirModal" class="btn btn-primary">
        Nuevo
    </button>

    <x-modal :show="$modalAbierto" max-width="md">
        <div class="flex justify-between items-center p-4 border-b border-base-content/10">
            <h3 class="text-lg font-semibold">Nuevo Operador</h3>
            <button type="button" wire:click="cerrarModal" class="btn btn-text btn-circle btn-sm" aria-label="Close">
                <span class="icon-[tabler--x] size-4"></span>
            </button>
        </div>

        <div class="p-4">
            <div class="font-semibold mb-4">
                <label class="label-text" for="nombre_operador">Nombre Operador</label>
                <input type="text" wire:model="nombre_operador" id="nombre_operador" class="input"
                    placeholder="Ingrese el nombre del operador">
                @error('nombre_operador')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label class="label-text font-semibold" for="">Número de Télefono (opcional)</label>
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
