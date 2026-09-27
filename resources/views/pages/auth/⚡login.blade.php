<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;


new #[Layout('layouts.guest')] class extends Component {
    public string $name = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'name' => 'required|string',
        'password' => 'required|string',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['name' => $this->name, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        $this->addError('email', 'Las credenciales no coinciden con nuestros registros.');
    }
};
?>

<div class="flex min-h-screen items-center justify-center bg-base-200 p-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title justify-center text-2xl font-bold">Iniciar Sesión</h2>

            <form wire:submit="login" class="space-y-4 mt-4">
                <!-- Campo Email -->
                <div class="form-control">
                    <label class="label"><span class="label-text">Usuario</span></label>
                    <input type="text" wire:model="name" class="input input-bordered w-full" placeholder="jlopes" />
                    @error('name') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Campo Contraseña -->
                <div class="form-control">
                    <label class="label"><span class="label-text">Contraseña</span></label>
                    <input type="password" wire:model="password" class="input input-bordered w-full" placeholder="••••••••" />
                    @error('password') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
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
        </div>
    </div>
</div>
