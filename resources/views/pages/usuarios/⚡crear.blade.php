<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Operador;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    public string $name = '';
    public string $nombre_completo = '';
    public string $email = '';
    public string $password = '';
    public string $selectedRole = '';
    public ?int $operador_id = null;

    public bool $esOperador = false;

    public function updatedSelectedRole($value): void
    {
        // Verifica si el rol seleccionado corresponde a Operador (insensible a mayúsculas/minúsculas)
        $role = Role::find($value);
        $this->esOperador = $role && strtolower($role->name) === 'operador';

        if (!$this->esOperador) {
            $this->operador_id = null;
        }
    }

    public function with(): array
    {
        return [
            'roles' => Role::all(),
            // Carga únicamente operadores que NO tienen un usuario asignado aún
            'operadoresDisponibles' => Operador::whereNull('user_id')
                ->orderBy('nombre_operador')
                ->get(),
        ];
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:users,name'],
            'nombre_completo' => ['required', 'string', 'max:255'],
            'email' => ['email', 'max:255'],
            'password' => ['required', Password::defaults()],
            'selectedRole' => ['required', 'exists:roles,id'],
        ];

        if ($this->esOperador) {
            $rules['operador_id'] = ['required', 'exists:operadores,id'];
        }

        $validated = $this->validate($rules);

        DB::transaction(function () use ($validated) {
            // 1. Crear el usuario
            $user = User::create([
                'name' => $validated['name'],
                'nombre_completo' => $validated['nombre_completo'],
                'email' => $validated['email'] ?? null,
                'password' => Hash::make($validated['password']),
            ]);

            // 2. Asignar el rol
            $role = Role::findById($validated['selectedRole']);
            $user->assignRole($role);

            // 3. Vincular con el operador en caso de aplicar
            if ($this->esOperador && $this->operador_id) {
                Operador::where('id', $this->operador_id)->update([
                    'user_id' => $user->id,
                ]);
            }
        });

        session()->flash('success', 'Usuario creado correctamente.');
        $this->redirect(route('usuarios'), navigate: true);
    }
}; ?>

<div class="card bg-base-100 max-w-3xl mx-auto shadow-sm border border-base-200 mt-5">
    <div class="card-body">
        <h2 class="card-title text-xl mb-4">Crear Nuevo Usuario</h2>

        <form wire:submit="save" class="space-y-4">

            <!-- Nombre de usuario (username) y Nombre Completo -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label label-text font-semibold">Usuario (Username)</label>
                    <input type="text" wire:model="name" class="input input-bordered w-full @error('name') input-error @enderror" placeholder="ej. jluna" />
                    @error('name') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="label label-text font-semibold">Nombre Completo</label>
                    <input type="text" wire:model="nombre_completo" class="input input-bordered w-full @error('nombre_completo') input-error @enderror" placeholder="ej. Jaime Luna" />
                    @error('nombre_completo') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Email y Password -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label label-text font-semibold">Correo Electrónico (Opcional)</label>
                    <input type="email" wire:model="email" class="input input-bordered w-full @error('email') input-error @enderror" placeholder="correo@ejemplo.com" />
                    @error('email') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="label label-text font-semibold">Contraseña</label>
                    <input type="password" wire:model="password" class="input input-bordered w-full @error('password') input-error @enderror" placeholder="••••••••" />
                    @error('password') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="divider my-2"></div>

            <!-- Selección de Rol -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label label-text font-semibold">Rol del Usuario</label>
                    <select wire:model.live="selectedRole" class="select select-bordered w-full @error('selectedRole') select-error @enderror">
                        <option value="">-- Seleccionar Rol --</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                    @error('selectedRole') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Campo Condicional: Selección de Operador -->
                @if ($esOperador)
                    <div class="animate-fadeIn">
                        <label class="label label-text font-semibold">Asignar Operador Disponible</label>
                        <select wire:model="operador_id" class="select select-bordered w-full @error('operador_id') select-error @enderror">
                            <option value="">-- Seleccionar Operador --</option>
                            @foreach ($operadoresDisponibles as $operador)
                                <option value="{{ $operador->id }}">
                                    {{ $operador->nombre_operador }} - {{ $operador->codigo_operador }}
                                </option>
                            @endforeach
                        </select>
                        @error('operador_id') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror

                        @if ($operadoresDisponibles->isEmpty())
                            <span class="text-warning text-xs mt-1 block">
                                No hay operadores pendientes de usuario disponible.
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Botones de Acción -->
            <div class="card-actions justify-end mt-6 gap-2">
                <a href="{{ route('usuarios') }}" class="btn btn-outline" wire:navigate>Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <span wire:loading.remove wire:target="save">Guardar Usuario</span>
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>

        </form>
    </div>
</div>
