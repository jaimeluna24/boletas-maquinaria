<?php

use Livewire\Component;
use App\Models\User;
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

    /**
     * Mapeo de campos ordenables
     */
    protected function sortableFields(): array
    {
        return [
            'id' => [
                'column' => 'users.id',
                'joins' => [],
            ],
            'name' => [
                'column' => 'users.name',
                'joins' => [],
            ],
            'nombre_completo' => [
                'column' => 'users.nombre_completo',
                'joins' => [],
            ],
            'email' => [
                'column' => 'users.email',
                'joins' => [],
            ],
            'created_at' => [
                'column' => 'users.created_at',
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

    public function with(): array
    {
        $query = User::query()->with('roles');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('nombre_completo', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhereHas('roles', function ($sub) {
                        $sub->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $config = $this->sortableFields();

        if (array_key_exists($this->sortField, $config)) {
            foreach ($config[$this->sortField]['joins'] as $applyJoin) {
                $applyJoin($query);
            }
            $query->orderBy($config[$this->sortField]['column'], $this->sortDirection)->select('users.*');
        } else {
            $query->orderBy('users.id', $this->sortDirection);
        }

        return [
            'usuarios' => $query->paginate(10),
        ];
    }

    public function crearUsuario()
    {
        return redirect()->route('usuario-crear');
    }

    public function editarUsuario($id)
    {
        return redirect()->route('users.edit', ['user' => $id]);
    }
};
?>

<div>
    <div class="w-full pl-6 pr-6 pt-5">
        <div class="flex gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <h4 class="text-xl font-semibold">Gestión de Usuarios</h4>

            <div class="flex gap-2">
                <div class="w-full max-w-md">
                    <input type="text" wire:model.live.debounce.200ms="search"
                        placeholder="Buscar por usuario, nombre, email o rol..." class="input input-md input-bordered w-full" />
                </div>
                <button wire:click="crearUsuario()" class="btn btn-primary btn-md">Nuevo Usuario</button>
            </div>
        </div>

        <div class="overflow-x-auto max-h-[73vh]">
            <table class="table-xs table table-striped">
                <thead style="font-size: 10pt; font-weight: 700;" class="sticky top-0 z-10">
                    <tr class="border-0 text-white bg-base-300 *:first:rounded-s-md *:last:rounded-e-md">
                        <th>#</th>
                        <x-th-sort field="name" label="Usuario" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="nombre_completo" label="Nombre Completo" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <x-th-sort field="email" label="Correo Electrónico" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <th class="font-semibold text-center">Rol(es)</th>
                        <x-th-sort field="created_at" label="Fecha Registro" :sortField="$sortField" :sortDirection="$sortDirection" />
                        <th class="font-semibold text-center">Acción</th>
                    </tr>
                </thead>

                <tbody class="text-base-content/80">
                    @forelse ($usuarios as $item)
                        <tr style="font-size: 11pt">
                            <th>
                                <label>{{ $loop->iteration + ($usuarios->currentPage() - 1) * $usuarios->perPage() }}</label>
                            </th>
                            <td class="font-mono font-medium">{{ $item->name }}</td>
                            <td>{{ $item->nombre_completo }}</td>
                            <td>{{ $item->email }}</td>
                            <td class="text-center">
                                @forelse ($item->roles as $role)
                                    <span class="badge badge-primary badge-soft text-xs">{{ ucfirst($role->name) }}</span>
                                @empty
                                    <span class="badge badge-ghost text-xs">Sin rol</span>
                                @endforelse
                            </td>
                            <td class="text-center whitespace-nowrap">
                                {{ $item->created_at ? $item->created_at->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <button wire:click="editarUsuario({{ $item->id }})"
                                    class="btn btn-circle btn-text btn-sm" aria-label="Editar">
                                    <span class="icon-[tabler--pencil] size-5"></span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center p-8 text-gray-500">
                                No se encontraron usuarios registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pb-4">
            {{ $usuarios->links('pagination::custom-pagination') }}
        </div>
    </div>
</div>
