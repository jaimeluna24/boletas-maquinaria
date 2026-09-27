<?php

use Livewire\Component;
use App\Models\TiempoPerdido;
use App\Models\TiempoPerdidoDetalle;
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
                'column' => 'tiempo_perdido_detalles.id',
                'joins' => [],
            ],
            'nombre_tiempo_perdido' => [
                'column' => 'tiempo_perdido_detalles.nombre_tiempo_perdido',
                'joins' => [],
            ],
            'distribucion_maquinaria_id' => [
                'column' => 'distribucion_maquinarias.descripcion',
                'joins' => [fn($q) => $q->leftJoin('distribucion_maquinarias', 'tiempo_perdido_detalles.distribucion_maquinaria_id', '=', 'distribucion_maquinarias.id')],
            ],
            'observacion' => [
                'column' => 'tiempo_perdido_detalles.observacion',
                'joins' => [],
            ],
            'fecha' => [
                'column' => 'tiempo_perdido_detalles.fecha',
                'joins' => [],
            ],
            'hora_inicio' => [
                'column' => 'tiempo_perdido_detalles.hora_inicio',
                'joins' => [],
            ],
            'hora_fin' => [
                'column' => 'tiempo_perdido_detalles.hora_fin',
                'joins' => [],
            ]
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

    #[On('tiempo-perdido-actualizado')]
    public function actualizarLista()
    {
        // Actualiza la lista de los tiempos perdidos despues de actualizar o crear
    }

    public function with(): array
    {
        $query = TiempoPerdidoDetalle::query()->with('tiempoPerdido', 'distribucionMaquinaria');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre_tiempo_perdido', 'like', '%' . $this->search . '%')
                    ->orWhere('observacion', 'like', '%' . $this->search . '%')
                    ->orWhereHas('distribucionMaquinaria', function ($sub) {
                        $sub->where('descripcion', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $config = $this->sortableFields();

        if (array_key_exists($this->sortField, $config)) {
            foreach ($config[$this->sortField]['joins'] as $applyJoin) {
                $applyJoin($query);
            }
            $query->orderBy($config[$this->sortField]['column'], $this->sortDirection)->select('tiempo_perdido_detalles.*');
        } else {
            $query->orderBy('tiempo_perdido_detalles.id', $this->sortDirection);
        }

        return [
            'tiempo_perdido_detalles' => $query->paginate(10),
        ];
    }
};
?>

<div>
    <div class="w-full pl-6 pr-6 pt-5">
        <div class="flex gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <h4 class="text-xl font-semibold">Gestión de Tiempo Perdido</h4>

            <div class="flex gap-2">
                <div class="w-full max-w-md">
                    <input type="text" wire:model.live.debounce.200ms="search"
                        placeholder="Buscar por nombre, actividad..."
                        class="input input-md input-bordered w-full" />
                </div>
                {{-- <livewire:pages::operadores.crear /> --}}
            </div>
        </div>

        <div class="overflow-x-auto max-h-[73vh]">
            <table class="table-xs table table-striped">
                <thead style="font-size: 10pt; font-weight: 700;" class="sticky top-0 z-10">
                    <tr class="border-0 text-white bg-base-300 *:first:rounded-s-md *:last:rounded-e-md">
                        <th>#</th>
                        <x-th-sort field="nombre_tiempo_perdido" label="Tiempo Perdido" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="distribucionMaquinaria.descripcion" label="Actividad" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="observacion" label="Observacion" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="fecha" label="Fecha" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="hora_inicio" label="hora Inicio" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="hora_fin" label="Hora Fin" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <th class="font-semibold text-center">Acción</th>
                    </tr>
                </thead>

                <tbody class="text-base-content/80">
                    @forelse ($tiempo_perdido_detalles as $item)
                        <tr style="font-size: 11pt" class="text-center">
                            <th>
                                <label>{{ $loop->iteration + ($tiempo_perdido_detalles->currentPage() - 1) * $tiempo_perdido_detalles->perPage() }}</label>
                            </th>
                            <td class="font-mono font-medium">{{ $item->nombre_tiempo_perdido }}</td>
                            <td>{{ $item->distribucionMaquinaria?->descripcion ?? 'N/A' }}</td>
                            <td>{{ $item->observacion ?? 'Sin Observación' }}</td>
                            <td>{{ $item->fecha ?? 'N/A' }}</td>
                            <td class="text-center">
                                {{ $item->hora_inicio ?? 'N/A' }}
                            </td>
                            <td class="text-center">
                                {{ $item->hora_fin ?? 'N/A' }}
                            </td>
                            <td class="whitespace-nowrap flex justify-center">
                                <livewire:pages::operadores.editar :id="$item->id" />
                                <livewire:pages::operadores.estado :id="$item->id" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center p-8 text-gray-500">
                                No se encontraron registros de tiempo perdido registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pb-4">
            {{ $tiempo_perdido_detalles->links('pagination::custom-pagination') }}
        </div>
    </div>
</div>
