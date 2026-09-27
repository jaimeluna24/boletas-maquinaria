<?php

use Livewire\Component;
use App\Models\Equipo;
use App\Models\TipoEquipo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $modalAbierto = false;

    public $id;
    public $inventario = '';
    public $nombre_equipo = '';
    public $activo = true;
    public $tipo_equipo_id = '';
    public $equipo;

    public function mount($id)
    {
        $this->id = $id;
    }

    protected function rules()
    {
        return [
            'nombre_equipo' => 'required|string|max:255',
            'inventario' => 'required|string|max:255',
            'tipo_equipo_id' => 'required|exists:tipo_equipos,id',
            'activo' => 'boolean',
        ];
    }

    public function abrirModal()
    {
        $this->resetFormulario();
        $this->equipo = Equipo::findOrFail($this->id);
        $this->inventario = $this->equipo->inventario;
        $this->nombre_equipo = $this->equipo->nombre_equipo;
        $this->activo = $this->equipo->activo;
        $this->tipo_equipo_id = $this->equipo->tipo_equipo_id;
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
            $this->equipo->update([
                'nombre_equipo' => $this->nombre_equipo,
                'inventario' => $this->inventario,
                'activo' => $this->activo,
                'tipo_equipo_id' => $this->tipo_equipo_id,
            ]);

            DB::commit();
            $this->dispatch('equipo-actualizado');
            $this->dispatch('notificar', type: 'success', title: 'Éxito:', message: 'Equipo editado correctamente.');
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
            \Log::error('Error al crear el equipo: ' . $e->getMessage());
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Ocurrió un error inesperado.', autoClose: false);

            \Log::error('Error inesperado al crear el equipo: ' . $e->getMessage());
        }
    }

    private function resetFormulario()
    {
        $this->reset(['nombre_equipo', 'inventario', 'tipo_equipo_id']);
        $this->activo = true;
        $this->resetValidation();
    }

    public function with(): array
    {
        return [
            'tipo_equipos' => TipoEquipo::all(),
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
            <h3 class="text-lg font-semibold">Editar Equipo</h3>
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
                <label class="label-text" for="">Nombre Equipo</label>
                <input type="text" wire:model="nombre_equipo" id="nombre_equipo" class="input"
                    placeholder="Nombre del Equipo">
                @error('nombre_equipo')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="text-left font-semibold mb-4">
                <label class="label-text" for="tipo_equipo_id">Tipo Equipo</label>
                <select wire:model="tipo_equipo_id" id="tipo_equipo_id" class="select">
                    <option value="">Seleccione el Tipo de Equipo</option>
                    @foreach ($tipo_equipos as $item)
                        <option value="{{ $item->id }}">{{ $item->nombre_tipo_equipo }}</option>
                    @endforeach
                </select>
                @error('tipo_equipo_id')
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
