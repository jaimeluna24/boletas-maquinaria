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
    public $nombre_operador = '';
    public $operador;
    public $estado_actual = '';

    public function mount($id)
    {
        $this->id = $id;
    }

    public function abrirModal()
    {
        $this->resetFormulario();
        $this->operador = Operador::findOrFail($this->id);
        $this->nombre_operador = $this->operador->nombre_operador;
        if ($this->operador->estado_operador == true) {
            $this->estado_actual = 'Desactivar';
        } else {
            $this->estado_actual = 'Activar';
        }
        $this->modalAbierto = true;
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetFormulario();
    }

    public function guardar()
    {
        DB::beginTransaction();

        try {
            $nuevoEstado = !$this->operador->estado_operador;

            $this->operador->update([
                'estado_operador' => $nuevoEstado,
            ]);

            DB::commit();
            $this->dispatch('operador-actualizado');
            $this->dispatch('notificar', type: 'warning', title: 'Advertencia:', message: 'Estado de operador cambiado correctamente.');
            $this->cerrarModal();
        } catch (QueryException $e) {
            // Código 1062 = entrada duplicada en MySQL
            DB::rollBack();

            if ($e->errorInfo[1] == 1062) {
                $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Ha ocurrido un error al realizar la acción.', autoClose: false);
            } else {
                $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Ocurrió un problema al cambiar el estado. Intenta nuevamente.', autoClose: false);
            }

            // Aquí conviene loguear el error real para revisarlo después
            \Log::error('Error al cambiar el estado del operador: ' . $e->getMessage());
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Ocurrió un error inesperado.', autoClose: false);
            dd($e->getMessage());
            \Log::error('Error inesperado al cambiar el estado del implemento: ' . $e->getMessage());
        }
    }

    private function resetFormulario()
    {
        $this->reset(['nombre_operador']);
        $this->activo = true;
        $this->resetValidation();
    }
};
?>

<div>
    <div class="text-center">
        <button wire:click="abrirModal" class="btn btn-circle btn-text btn-sm text-red-700">
            <span class="icon-[at-icons--trash-can] size-5"></span>
        </button>
    </div>


    <x-modal :show="$modalAbierto" max-width="md">
        <div class="flex justify-between items-center p-4 pb-0">
            <h3 class="text-lg font-semibold"></h3>
            <button type="button" wire:click="cerrarModal" class="btn btn-text btn-circle btn-sm" aria-label="Close">
                <span class="icon-[tabler--x] size-4"></span>
            </button>
        </div>

        <div class="pb-1 flex justify-center">
            <span class="icon-[at-icons--trash-can] size-20"></span>
        </div>
        <div class="flex flex-col items-center">
            <h3 class="text-lg font-semibold">Cambiar Estado del Operador {{ $nombre_operador }}</h3>
            <h3 class="text-lg">¿Estás seguro de {{ $estado_actual }} del Operador?</h3>
        </div>

        <div class="flex justify-end gap-2 p-4">
            <button type="button" wire:click="cerrarModal" class="btn btn-default">
                Cancelar
            </button>
            <button type="button" wire:click="guardar" class="btn btn-accent">
                {{ $estado_actual }}
            </button>
        </div>
    </x-modal>
</div>
