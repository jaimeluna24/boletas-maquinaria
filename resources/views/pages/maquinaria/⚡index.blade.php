<?php

use Livewire\Component;
use App\Models\Equipo;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

new class extends Component {
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $sortField = 'id';

    #[Url]
    public $sortDirection = 'desc';

    /**
    * Mapeo de campos ordenables
    */
    protected function sortableFields(): array
    {
        return [
            'id' => [
                'column' => 'equipos.id',
                'joins' => [],
            ],
            'inventario' => [
                'column' => 'equipos.inventario',
                'joins' => [],
            ],
            'nombre_equipo' => [
                'column' => 'equipos.nombre_equipo',
                'joins' => [],
            ],
            'tipo_equipo_id' => [
                'column' => 'tipo_equipos.nombre_tipo_equipo',
                'joins' => [fn($q) => $q->leftJoin('tipo_equipos', 'equipos.tipo_equipo_id', '=', 'tipo_equipos.id')],
            ],
            'activo' => [
                'column' => 'equipos.activo',
                'joins' => [],
            ],
        ];
    }

    public function sortBy($field)
    {
        if (!array_key_exists($field, $this->sortableFields())) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[On('equipo-actualizado')]
    public function actualizarLista()
    {
        // Actualiza la lista de equipos despues de actualizar o crear equipos
    }

    public function with(): array
    {
        $query = Equipo::query()->with('tipoEquipo');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('inventario', 'like', '%' . $this->search . '%')
                    ->orWhere('nombre_equipo', 'like', '%' . $this->search . '%')
                    ->orWhereHas('tipoEquipo', function ($sub) {
                        $sub->where('nombre_tipo_equipo', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $config = $this->sortableFields();

        if (array_key_exists($this->sortField, $config)) {
            foreach ($config[$this->sortField]['joins'] as $applyJoin) {
                $applyJoin($query);
            }
            $query->orderBy($config[$this->sortField]['column'], $this->sortDirection)->select('equipos.*');
        } else {
            $query->orderBy('equipos.id', $this->sortDirection);
        }

        return [
            'equipos' => $query->paginate(10),
        ];
    }
};
?>

<div>
    <div class="w-full pl-6 pr-6 pt-5">
        <div class="flex gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <h4 class="text-xl font-semibold">Gestión de Maquinaria</h4>

            <div class="flex gap-2">
                <div class="w-full max-w-md">
                    <input type="text" wire:model.live.debounce.200ms="search"
                        placeholder="Buscar por equipo..."
                        class="input input-md input-bordered w-full" />
                </div>
                <livewire:pages::maquinaria.crear />

            </div>
        </div>

        <div class="overflow-x-auto max-h-[73vh]">
            <table class="table-xs table table-striped">
                <thead style="font-size: 10pt; font-weight: 700;" class="sticky top-0 z-10">
                    <tr class="border-0 text-white bg-base-300 *:first:rounded-s-md *:last:rounded-e-md">
                        <th>#</th>
                        <x-th-sort field="inventario" label="Inventario" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="nombre_equipo" label="Equipo" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="tipoEquipo.nombre_tipo_equipo" label="Tipo Equipo" :sortField="$sortField"
                            :sortDirection="$sortDirection" />
                        <x-th-sort field="activo" label="Estado" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <th class="font-semibold text-center">Acción</th>
                    </tr>
                </thead>

                <tbody class="text-base-content/80">
                    @forelse ($equipos as $item)
                        <tr style="font-size: 11pt" class="text-center">
                            <th>
                                <label>{{ $loop->iteration + ($equipos->currentPage() - 1) * $equipos->perPage() }}</label>
                            </th>
                            <td class="font-mono font-medium">{{ $item->inventario }}</td>
                            <td>{{ $item->nombre_equipo }}</td>
                            <td>{{ $item->tipoEquipo->nombre_tipo_equipo }}</td>
                            <td class="text-center">
                                {{ $item->activo ? 'Activo' : 'Inactivo' }}
                            </td>
                            <td class="whitespace-nowrap flex justify-center">
                                <livewire:pages::maquinaria.editar :id="$item->id" />
                                <livewire:pages::maquinaria.eliminar :id="$item->id" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center p-8 text-gray-500">
                                No se encontraron equipos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pb-4">
            {{ $equipos->links('pagination::custom-pagination') }}
        </div>
    </div>
</div>
