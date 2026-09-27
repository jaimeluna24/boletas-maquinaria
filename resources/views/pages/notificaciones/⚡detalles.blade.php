<?php

use Livewire\Component;
use App\Models\Notificacion;
use App\Models\NotificacionUsuario;

new class extends Component
{
    public $notificacion;

    public function mount($id)
    {
        $this->notificacion = Notificacion::find($id);
        if ($this->notificacion->leido_at == null) {
            $this->notificacion->notificacionesUsuarios()
                ->where('user_id', auth()->id())
                ->update(['leido_at' => now()]);
        }
    }
};
?>

<div>
    {{-- Act only according to that maxim whereby you can, at the same time, will that it should become a universal law. - Immanuel Kant --}}
</div>
