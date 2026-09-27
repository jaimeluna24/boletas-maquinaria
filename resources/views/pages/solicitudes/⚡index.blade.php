<?php

use Livewire\Component;
use App\Models\Solicitud;
use App\Models\Actividad;
use App\Models\User;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

new class extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $sortField = 'id';

    #[Url]
    public $sortDirection = 'desc';

    /**
     * Configuración de campos ordenables.
     * Mapea columnas locales y joins necesarios para relaciones.
     */
    protected function sortableFields(): array
    {
        return [
            // Campos directos de solicitudes
            'codigo_solicitud' => [
                'column' => 'solicitudes.codigo_solicitud',
                'joins' => [],
            ],
            'fecha_solicitud' => [
                'column' => 'solicitudes.fecha_solicitud',
                'joins' => [],
            ],
            'sector' => [
                'column' => 'solicitudes.sector',
                'joins' => [],
            ],
            'tipo_equipo' => [
                'column' => 'solicitudes.tipo_equipo',
                'joins' => [],
            ],
            'descripcion' => [
                'column' => 'solicitudes.descripcion',
                'joins' => [],
            ],
            'estado' => [
                'column' => 'solicitudes.estado',
                'joins' => [],
            ],

            // Relación 1 nivel: Actividades
            'actividad_id' => [
                'column' => 'actividades.nombre_actividad',
                'joins' => [
                    fn ($q) => $q->leftJoin('actividades', 'solicitudes.actividad_id', '=', 'actividades.id'),
                ],
            ],

            // Relación 1 nivel: Usuarios Solicitantes
            'solicitante' => [
                'column' => 'users.nombre_completo',
                'joins' => [
                    fn ($q) => $q->leftJoin('users', 'solicitudes.solicitante', '=', 'users.id'),
                ],
            ],
        ];
    }

    public function sortBy($field)
    {
        if (! array_key_exists($field, $this->sortableFields()) && $field !== 'id') {
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
        $query = Solicitud::query()
            ->with(['actividad', 'usuarioSolicitante']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo_solicitud', 'like', '%' . $this->search . '%')
                  ->orWhere('sector', 'like', '%' . $this->search . '%')
                  ->orWhere('tipo_equipo', 'like', '%' . $this->search . '%')
                  ->orWhereHas('actividad', function ($sub) {
                      $sub->where('nombre_actividad', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('usuarioSolicitante', function ($sub) {
                      $sub->where('nombre_completo', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $config = $this->sortableFields();

        if (array_key_exists($this->sortField, $config)) {
            foreach ($config[$this->sortField]['joins'] as $applyJoin) {
                $applyJoin($query);
            }
            $query->orderBy($config[$this->sortField]['column'], $this->sortDirection)
                  ->select('solicitudes.*');
        } else {
            $query->orderBy('solicitudes.id', $this->sortDirection);
        }

        return [
            'solicitudes' => $query->paginate(10),
        ];
    }

    public function detalleSolicitud($id)
    {
        return redirect()->route('detalle-solicitud', ['id' => $id]);
    }
};
?>

<div>
    <div class="w-full pl-6 pr-6 pt-5">
        <div class="flex gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <h4 class="text-xl font-semibold">Mis Solicitudes</h4>

            <div class="flex gap-2">
                <div class="w-full max-w-md">
                    <input
                        type="text"
                        wire:model.live.debounce.200ms="search"
                        placeholder="Buscar solicitud, actividad o solicitante..."
                        class="input input-md input-bordered w-full"
                    />
                </div>
            </div>
        </div>

        <div class="overflow-x-auto max-h-[73vh]">
            <table class="table-xs table table-striped">
                <thead style="font-size: 10pt; font-weight: 700;" class="sticky top-0 z-10">
                    <tr class="border-0 text-white bg-base-300 *:first:rounded-s-md *:last:rounded-e-md">
                        <th>#</th>
                        <x-th-sort field="codigo_solicitud" label="Solicitud" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="actividad_id" label="Actividad" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="fecha_solicitud" label="Fecha" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="sector" label="Sector" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="tipo_equipo" label="Equipo" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="solicitante" label="Solicitante" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="estado" label="Estado" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <th class="font-semibold text-center">Acción</th>
                    </tr>
                </thead>

                <tbody class="text-base-content/80">
                    @forelse ($solicitudes as $item)
                        <tr style="font-size: 11pt">
                            <th>
                                <label>{{ $loop->iteration + ($solicitudes->currentPage() - 1) * $solicitudes->perPage() }}</label>
                            </th>
                            <td class="text-center font-mono font-medium">{{ $item->codigo_solicitud }}</td>
                            <td>{{ $item->actividad?->nombre_actividad ?? 'N/A' }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($item->fecha_solicitud)->format('d/m/Y') }}</td>
                            <td class="text-center truncate">{{ $item->sector }}</td>
                            <td>{{ $item->tipo_equipo }}</td>
                            <td>{{ $item->usuarioSolicitante?->nombre_completo ?? 'N/A' }}</td>
                            <td class="text-center">
                                @switch($item->estado)
                                    @case('Aprobada')
                                        <span class="badge badge-success">Aprobada</span>
                                        @break
                                    @case('Rechazada')
                                        <span class="badge badge-error">Rechazada</span>
                                        @break
                                    @default
                                        <span class="badge badge-secundary">{{ $item->estado ?? 'Pendiente' }}</span>
                                @endswitch
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <button wire:click="detalleSolicitud({{ $item->id }})" class="btn btn-circle btn-text btn-sm" aria-label="Eliminar">
                                    <span class="icon-[ooui--view-details-rtl] size-5"></span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center p-8 text-gray-500">
                                No hay solicitudes registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pb-4">
            {{ $solicitudes->links('pagination::custom-pagination') }}
        </div>
    </div>
</div>
