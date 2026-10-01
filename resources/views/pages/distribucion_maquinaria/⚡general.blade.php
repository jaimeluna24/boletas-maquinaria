<?php

use Livewire\Component;
use App\Models\DistribucionMaquinaria;
use App\Models\Solicitud;
use App\Models\Actividad;
use App\Models\User;
use App\Models\Equipo;
use App\Models\Operador;
use App\Models\Implemento;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

new class extends Component {
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $sortField = 'id';

    #[Url]
    public $sortDirection = 'desc';

    // Resetear la paginación cuando cambia la fecha
    public function updatingFecha()
    {
        $this->resetPage();
    }

    /**
     * Configuración de campos ordenables.
     * Mapea columnas locales y joins necesarios para relaciones.
     */
    protected function sortableFields(): array
    {
        return [
            // Campos directos de solicitudes
            'nombre_operador' => [
                'column' => 'operadores.nombre_operador',
                'joins' => [fn($q) => $q->leftJoin('operadores', 'distribucion_maquinarias.operador_id', '=', 'operadores.id')],
            ],
            'inventario' => [
                'column' => 'equipos.inventario',
                'joins' => [fn($q) => $q->leftJoin('equipos', 'distribucion_maquinarias.equipo_id', '=', 'equipos.id')],
            ],
            // 'nombre_actividad' => [
            //     'column' => 'actividades.nombre_actividad',
            //     'joins' => [fn($q) => $q->leftJoin('solicitudes', 'distribucion_maquinarias.solicitud_id', '=', 'solicitudes.id'), fn($q) => $q->leftJoin('actividades', 'solicitudes.actividad_id', '=', 'actividades.id')],
            // ],
            'descripcion' => [
                'column' => 'distribucion_maquinarias.descripcion',
                'joins' => [],
            ],
            'lugar' => [
                'column' => 'distribucion_maquinarias.lugar',
                'joins' => [],
            ],
            'hora_inicio' => [
                'column' => 'distribucion_maquinarias.hora_inicio',
                'joins' => [],
            ],
            'estado' => [
                'column' => 'distribucion_maquinarias.estado',
                'joins' => [],
            ],
        ];
    }

    public function sortBy($field)
    {
        if (!array_key_exists($field, $this->sortableFields()) && $field !== 'id') {
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

    public function with(): array
    {
        $query = DistribucionMaquinaria::query()->with(['operador', 'solicitud', 'equipo', 'implemento', 'tiempoPerdidoDetalle']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('lugar', 'like', '%' . $this->search . '%')
                    ->orWhereHas('operador', function ($sub) {
                        $sub->where('nombre_operador', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('equipo', function ($sub) {
                        $sub->where('inventario', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $config = $this->sortableFields();

        if (array_key_exists($this->sortField, $config)) {
            foreach ($config[$this->sortField]['joins'] as $applyJoin) {
                $applyJoin($query);
            }
            $query->orderBy($config[$this->sortField]['column'], $this->sortDirection)->select('distribucion_maquinarias.*');
        } else {
            $query->orderBy('distribucion_maquinarias.id', $this->sortDirection);
        }

        return [
            'distribucion_maquinarias' => $query->paginate(10),
        ];
    }

    public function detalleDistribucion($id)
    {
        return redirect()->route('detalle-distribucion', ['id' => $id]);
    }
};
?>

<div>
    <div class="w-full pl-6 pr-6 pt-5">
        <div class="flex gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <h4 class="text-xl font-semibold">Distribución de Maquinaria Historico</h4>

            <div class="flex gap-2">
                <div class="w-full max-w-md">
                    <input type="text" wire:model.live.debounce.200ms="search"
                        placeholder="Buscar por Operador, Equipo" class="input input-md input-bordered w-full" />
                </div>

            </div>
        </div>

        <div class="overflow-x-auto max-h-[73vh]">
            <table class="table-xs table table-striped">
                <thead style="font-size: 10pt; font-weight: 700;" class="sticky top-0 z-10">
                    <tr class="border-0 text-white bg-base-300 *:first:rounded-s-md *:last:rounded-e-md">
                        <th>#</th>
                        <x-th-sort field="nombre_operador" label="Operador" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="inventario" label="Equipo" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="descripcion" label="Actividad" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="lugar" label="Lugar" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="hora_inicio" label="Hora" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="estado" label="Estado" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <th class="font-semibold text-center">Acción</th>
                    </tr>
                </thead>

                <tbody class="text-base-content/80">
                    @forelse ($distribucion_maquinarias as $item)
                        <tr style="font-size: 11pt">
                            <th>
                                <label>{{ $loop->iteration + ($distribucion_maquinarias->currentPage() - 1) * $distribucion_maquinarias->perPage() }}</label>
                            </th>
                            <td class="text-center font-mono font-medium">{{ $item->operador->nombre_operador }}</td>
                            <td>{{ $item->equipo->inventario ?? 'N/A' }}</td>
                            <td>{{ $item->descripcion ?? 'N/A' }}</td>
                            <td>{{ $item->lugar }}</td>
                            {{-- <td class="text-center">{{ \Carbon\Carbon::parse($item->fecha_solicitud)->format('d/m/Y') }}
                            </td> --}}
                            <td class="text-center truncate">{{ $item->fecha }}</td>
                            <td class="text-center">
                                @switch($item->estado)
                                    @case('Completada')
                                        <span class="badge badge-success">Completada</span>
                                    @break

                                    @case('Pendiente')
                                        <span class="badge badge-secundary">Pendiente</span>
                                    @break

                                    @case('En Proceso')
                                        <span class="badge badge-info">En Proceso</span>
                                    @break

                                    @default
                                        <span class="badge badge-secundary">{{ $item->estado ?? 'Pendiente' }}</span>
                                @endswitch
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <button wire:click="detalleSolicitud({{ $item->id }})"
                                    class="btn btn-circle btn-text btn-sm" aria-label="Eliminar">
                                    <span class="icon-[ooui--view-details-rtl] size-5"></span>
                                </button>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center p-8 text-gray-500">
                                    No hay distribuciones registradas para la fecha. {{ $fecha }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 pb-4">
                {{ $distribucion_maquinarias->links('pagination::custom-pagination') }}
            </div>
        </div>
    </div>
