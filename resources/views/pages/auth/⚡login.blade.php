<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Livewire\Attributes\Layout;

new #[Layout('layouts.guest')] class extends Component {
    public string $name = '';
    public string $password = '';
    public bool $remember = false;
    public bool $usuarioEncontrado = false;

    protected array $rules = [
        'name' => 'required|string',
        'password' => 'required|string',
    ];

    public function buscarUsuario()
    {
        $usuario = User::where('name', $this->name)->first();
        if ($usuario) {
            $this->usuarioEncontrado = true;
            $this->dispatch('notificar', type: 'success', title: 'Éxito', message: 'Usuario Encontrado.');
        } else {
            $this->dispatch('notificar', type: 'error', title: 'Error', message: 'Usuario no Encontrado.');
        }
    }

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['name' => $this->name, 'password' => $this->password], $this->remember)) {
            $this->dispatch('notificar', type: 'success', title: 'Éxito', message: 'Iniciando Sesión.');
            session()->regenerate();

            return redirect()->intended('/distribuciones-diarias');

        } else {
            $this->dispatch('notificar', type: 'error', title: 'Error:', message: 'Contraseña incorrecta intentelo de nuevo.');
        }

        $this->addError('email', 'Las credenciales no coinciden con nuestros registros.');
    }
};
?>

<div class="flex min-h-screen items-center justify-center bg-base-200 p-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body">
             <div class="flex justify-center items-center">
                <img src="{{ asset('images/logo_achsa_light.jpg') }}" alt="Logo" class="logo-light" style="width: 200px">
                <img src="{{ asset('images/logo_achsa_dark.png') }}" alt="Logo" class="logo-dark">
            </div>
            {{-- <div class="flex justify-center items-center">
            <h2 class="card-title justify-center text-2xl font-bold">Iniciar Sesión</h2>
            </div> --}}

            @if ($usuarioEncontrado === false)
                <form wire:submit="buscarUsuario" class="space-y-4 mt-4">
                    <!-- Campo Email -->
                    <div class="form-control">
                        <label class="label"><span class="label-text">Usuario</span></label>
                        <input type="text" wire:model="name" class="input input-bordered w-full"
                            placeholder="eaguilera" />
                        @error('name')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Botón Submit -->
                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary w-full">
                            <span wire:loading.remove>Buscar</span>
                            <span wire:loading class="loading loading-spinner"></span>
                        </button>
                    </div>
                </form>
            @else
                <form wire:submit="login" class="space-y-4 mt-4">
                    <!-- Campo Email -->
                    <div class="form-control">
                        <label class="label"><span class="label-text">Usuario</span></label>
                        <input type="text" wire:model="name" class="input input-bordered w-full"
                            placeholder="eaguilera" />
                        @error('name')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Contraseña</span>
                        </label>

                        <div class="relative" x-data="{ mostrar: false }">

                            <input :type="mostrar ? 'text' : 'password'" wire:model="password"
                                class="input input-bordered w-full pr-10" placeholder="••••••••" />

                            <button type="button" @click="mostrar = !mostrar"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-base-content"
                                aria-label="Mostrar contraseña">
                                <span x-show="!mostrar" class="icon-[tabler--eye] size-5"></span>

                                <span x-show="mostrar" class="icon-[tabler--eye-off] size-5"></span>
                            </button>

                        </div>

                        @error('password')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Recordarme -->
                    <div class="form-control">
                        <label class="cursor-pointer label justify-start gap-2">
                            <input type="checkbox" wire:model="remember" class="checkbox checkbox-primary" />
                            <span class="label-text">Recordarme</span>
                        </label>
                    </div>

                    <!-- Botón Submit -->
                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary w-full">
                            <span wire:loading.remove>Ingresar</span>
                            <span wire:loading class="loading loading-spinner"></span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
    <style>
        .logo-dark {
            display: none;
            width: 200px;
        }

        [data-theme="dark"] .logo-light {
            display: none;
        }

        [data-theme="dark"] .logo-dark {
            display: block;
        }
    </style>
