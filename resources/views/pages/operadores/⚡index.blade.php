<?php

use Livewire\Component;
use App\Models\Operador;
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
                'column' => 'operadores.id',
                'joins' => [],
            ],
            'codigo_operador' => [
                'column' => 'operadores.codigo_operador',
                'joins' => [],
            ],
            'nombre_operador' => [
                'column' => 'operadores.nombre_operador',
                'joins' => [],
            ],
            'telefono_operador' => [
                'column' => 'operadores.telefono_operador',
                'joins' => [],
            ],
            'estado_operador' => [
                'column' => 'operadores.estado_operador',
                'joins' => [],
            ],
            'user_id' => [
                'column' => 'users.name',
                'joins' => [fn($q) => $q->leftJoin('users', 'operadores.users_id', '=', 'users.id')],
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

    #[On('operador-actualizado')]
    public function actualizarLista()
    {
        // Actualiza la lista de operadores despues de actualizar o crear operadores
    }

    public function with(): array
    {
        $query = Operador::query()->with('user');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo_operador', 'like', '%' . $this->search . '%')
                    ->orWhere('nombre_operador', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($sub) {
                        $sub->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $config = $this->sortableFields();

        if (array_key_exists($this->sortField, $config)) {
            foreach ($config[$this->sortField]['joins'] as $applyJoin) {
                $applyJoin($query);
            }
            $query->orderBy($config[$this->sortField]['column'], $this->sortDirection)->select('operadores.*');
        } else {
            $query->orderBy('operadores.id', $this->sortDirection);
        }

        return [
            'operadores' => $query->paginate(10),
        ];
    }
};
?>

<div>
    <div class="w-full pl-6 pr-6 pt-5">
        <div class="flex gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <h4 class="text-xl font-semibold">Gestión de Operadores</h4>

            <div class="flex gap-2">
                <div class="w-full max-w-md">
                    <input type="text" wire:model.live.debounce.200ms="search"
                        placeholder="Buscar por nombre, código o usuario..."
                        class="input input-md input-bordered w-full" />
                </div>
                <livewire:pages::operadores.crear />

            </div>
        </div>

        <div class="overflow-x-auto max-h-[73vh]">
            <table class="table-xs table table-striped">
                <thead style="font-size: 10pt; font-weight: 700;" class="sticky top-0 z-10">
                    <tr class="border-0 text-white bg-base-300 *:first:rounded-s-md *:last:rounded-e-md">
                        <th>#</th>
                        <x-th-sort field="codigo_operador" label="Código" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="nombre_operador" label="Operador" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="user.name" label="Usuario sistema" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="telefono_operador" label="Telefono" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="estado_operador" label="Estado" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <th class="font-semibold text-center">Acción</th>
                    </tr>
                </thead>

                <tbody class="text-base-content/80">
                    @forelse ($operadores as $item)
                        <tr style="font-size: 11pt" class="text-center">
                            <th>
                                <label>{{ $loop->iteration + ($operadores->currentPage() - 1) * $operadores->perPage() }}</label>
                            </th>
                            <td class="font-mono font-medium">{{ $item->codigo_operador }}</td>
                            <td>{{ $item->nombre_operador }}</td>
                            <td>{{ $item->user->name ?? 'Sin Asignación' }}</td>
                            <td>{{ $item->telefono_operador ?? 'N/A' }}</td>
                            <td class="text-center">
                                {{ $item->estado_operador ? 'Activo' : 'Inactivo' }}
                            </td>
                            <td class="whitespace-nowrap flex justify-center">
                                <livewire:pages::operadores.editar :id="$item->id" />
                                <livewire:pages::operadores.estado :id="$item->id" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center p-8 text-gray-500">
                                No se encontraron operadores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pb-4">
            {{ $operadores->links('pagination::custom-pagination') }}
        </div>
    </div>
</div>
