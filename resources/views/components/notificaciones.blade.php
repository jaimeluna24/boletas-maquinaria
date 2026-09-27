<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\NotificacionUsuario;

new class extends Component {
    use WithPagination;

    public $notificaciones = [];
    public $cantidad = 0;

    public function mount()
    {
        $this->notificaciones = NotificacionUsuario::with(['notificacion', 'user'])
            ->where('user_id', auth()->id())
            ->latest()
            ->take(10)
            ->get();

        $this->cantidad = NotificacionUsuario::where('user_id', auth()->id())
            ->whereNull('leido_at')
            ->count();
    }

    public function detalleNotificacion($id)
    {
        return redirect()->route('notificacion-detalle', ['id' => $id]);
    }
};
?>

<div>
    <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end] z-20">
        <button id="dropdown-scrollable" type="button"
            class="dropdown-toggle btn btn-text btn-circle dropdown-open:bg-base-content/10 size-10" aria-haspopup="menu"
            aria-expanded="false" aria-label="Dropdown">
            <div class="indicator">
                @if ($cantidad > 0)
                    <span class="indicator-item badge badge-error rounded-full size-5 text-xs">
                        {{ $cantidad }}
                        <span class="bg-error absolute size-full animate-ping rounded-full opacity-75"></span>
                    </span>
                @endif
                <span class="icon-[tabler--bell] text-base-content size-6.5"></span>
            </div>

        </button>
        <div class="dropdown-menu dropdown-open:opacity-100 hidden" role="menu" aria-orientation="vertical"
            aria-labelledby="dropdown-scrollable">
            <div class="dropdown-header justify-center">
                <h6 class="text-base-content text-base">
                    Notificaciones
                </h6>
            </div>
            <div class="overflow-auto text-base-content/80 max-h-56 max-md:max-w-60">
                @forelse ($notificaciones as $item)
                    @php
                        $leido = $item->leido_at !== null;
                    @endphp

                    <button wire:click="detalleNotificacion({{ $item->notificacion_id }})"
                        class="mb-1 dropdown-item w-full flex items-center gap-3 p-2 text-left transition-colors {{ $leido ? 'bg-base-200/60 opacity-75' : 'bg-base-100 font-semibold' }}">
                        <div
                            class="flex items-center justify-center shrink-0 w-10 h-10 rounded-full {{ $leido ? 'bg-base-300 text-base-content/60' : 'bg-primary/10 text-primary' }}">
                            @if ($leido)
                                <!-- Notificación leída -->
                                <span class="icon-[tabler--mail-opened] size-5"></span>
                            @else
                                <!-- Notificación no leída -->
                                <span class="icon-[tabler--bell-ringing] size-5"></span>
                            @endif
                        </div>
                        <div class="w-60 overflow-hidden">
                            <h6
                                class="truncate text-base {{ $leido ? 'text-base-content/70' : 'text-base-content font-bold' }}">
                                {{ $item->notificacion->titulo }}
                            </h6>

                            <small class="text-base-content/50 truncate block">
                                {{ $item->notificacion->mensaje }}
                            </small>
                        </div>
                    </button>
                @empty
                    <div class="dropdown-item justify-center">
                        <span class="text-gray-500">
                            No hay notificaciones.
                        </span>
                    </div>
                @endforelse
            </div>
            <a href="#" class="dropdown-footer justify-center gap-1">
                <span class="icon-[tabler--eye] size-4"></span>
                Ver Todas
            </a>
        </div>

    </div>

</div>
