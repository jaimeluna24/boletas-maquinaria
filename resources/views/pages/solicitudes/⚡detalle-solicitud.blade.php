<?php

use Livewire\Component;
use App\Models\Actividad;
use App\Models\TipoEquipo;
use App\Models\Solicitud;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $solicitud;
    public $responder = false;
    public $estado = '';
    public $observacion = '';

    public function mount($id)
    {
        $this->solicitud = Solicitud::find($id);
    }

    public function respoderSolicitud($estado)
    {
        $this->responder = true;
        $this->estado = $estado;
    }

    public function cancelar()
    {
        $this->responder = false;
        $this->estado = '';
    }

    public function cambiarEstado()
    {
        $this->solicitud->estado = $this->estado;
        $this->solicitud->observacion = $this->observacion;
        $this->solicitud->contestada_por = Auth::user()->name;

        $this->solicitud->save();
        $this->responder = false;
        $this->estado = '';
        $this->observacion = '';
    }
};
?>

<div class="p-10">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold">Detalles de Solicitud {{ $solicitud->codigo_solicitud }}</h3>
    </div>

    <form class="mt-6">
        <div class="grid grid-cols-3 mt-4 pl-5 pr-5 w-full gap-2">
            <div class="w-auto col-span-1">
                <label class="label-text" for="defaultInput">Actividad</label>
                <div class="flex gap-2">
                    <div class="relative w-full">
                        <input disabled
                            value="{{ $solicitud->actividad->codigo_actividad }} - {{ $solicitud->actividad->nombre_actividad }}"
                            type="text"
                            class="input disabled:bg-base-200/40 disabled:text-base-content/60 disabled:border-base-content/20"
                            id="defaultInput" />
                    </div>
                </div>
            </div>
            <div class="w-auto col-span-1">
                <label class="label-text" for="defaultInput">Equipo Solicitado</label>
                <div class="flex gap-2">
                    <div class="relative w-full">
                        <input disabled value="{{ $solicitud->tipo_equipo }}" type="text"
                            class="input disabled:bg-base-200/40 disabled:text-base-content/60 disabled:border-base-content/20"
                            id="defaultInput" />
                    </div>
                </div>
            </div>

            <div class="w-auto col-span-1">
                <label class="label-text" for="defaultInput">Fecha Solicitado</label>
                <div class="flex gap-2">
                    <div class="relative w-full">
                        <input disabled value="{{ $solicitud->fecha_solicitud }}" type="text"
                            class="input disabled:bg-base-200/40 disabled:text-base-content/60 disabled:border-base-content/20"
                            id="defaultInput" />
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 mt-4 pl-5 pr-5 w-full gap-4">
            <div class="w-full col-span-1">
                <div>
                    <label class="label-text" for="textareaLabel">Sector</label>
                    <textarea disabled
                        class="textarea input w-full py-2 bg-base-200/50 text-base-content/60 border-base-content/20 disabled:bg-base-200/40 disabled:text-base-content/60 disabled:border-base-content/20"
                        placeholder="Describa el sector" id="textareaLabel">{{ trim($solicitud->sector) }}</textarea>
                </div>
            </div>

            <div class="w-full col-span-1">
                <div>
                    <label class="label-text" for="textareaLabel">Descripción</label>
                    <textarea disabled
                        class="textarea input w-full py-2 bg-base-200/50 text-base-content/60 border-base-content/20 disabled:bg-base-200/40 disabled:text-base-content/60 disabled:border-base-content/20"
                        placeholder="Describa el sector" id="textareaLabel">{{ trim($solicitud->descripcion) }}</textarea>
                </div>
            </div>
        </div>
        @if ($responder == true)
            <div class="flex justify-between items-center pt-5 pl-5">
                <h3 class="text-lg font-semibold">Solicitud en estado: {{ $estado }}</h3>
            </div>
            <div class="grid grid-cols-1 mt-4 pl-5 pr-5 w-full gap-4">
                <div class="w-full col-span-1">
                    <div>
                        <label class="label-text" for="textareaLabel">Observación</label>
                        <textarea wire:model="observacion"
                            class="textarea input w-full py-2 bg-base-200/50 text-base-content/60 border-base-content/20 disabled:bg-base-200/40 disabled:text-base-content/60 disabled:border-base-content/20"
                            placeholder="Escriba la observación (opcional)" id="textareaLabel"></textarea>
                    </div>
                </div>
            </div>
            <div class="card-actions justify-end mt-6 gap-2">
                <button wire:click.prevent="cancelar()" class="btn btn-secondary">Cancelar</butt>
                    <button wire:click="cambiarEstado" class="btn btn-accent" type="button"
                        wire:loading.attr="disabled" wire:target="cambiarEstado">
                        <span wire:target="cambiarEstado" wire:loading.remove>Guardar</span>
                        <span wire:target="cambiarEstado" wire:loading
                            class="loading loading-spinner">Guardando...</span>
                    </button>
            </div>
        @else
            @if ($solicitud->estado == 'Pendiente')
                <div class="flex justify-between items-center pt-5 pl-5">
                    <div class="alert alert-soft alert-primary" role="alert">
                        <h3 class="text-lg font-semibold">Solicitud Pendiente de Respuesta</h3>
                    </div>
                </div>

                @hasanyrole('Administrador|Gestion')
                    <div class="card-actions justify-end mt-6 gap-2">
                        <a href="{{ route('solicitudes') }}"class="btn btn-secondary">Cancelar</a>
                        @if ($solicitud->solicitante != Auth::user()->id)
                            <button wire:click="respoderSolicitud('Rechazada')" class="btn btn-error" type="button">
                                <span>Rechazar</span>
                            </button>
                            <button wire:click="respoderSolicitud('Aprobada')" class="btn btn-success" type="button">
                                <span>Aprobar</span>
                            </button>
                        @endif
                    </div>
                @endhasanyrole
            @elseif ($solicitud->estado == 'Aprobada')
                <div class="flex justify-between items-center pt-5 pl-5">
                    <div class="alert alert-soft alert-success" role="alert">
                        <h3 class="text-lg font-semibold">La solicitud a sido {{ $solicitud->estado }} por el usuario
                            {{ $solicitud->contestada_por }}</h3>
                    </div>
                </div>
                <div class="grid grid-cols-1 mt-4 pl-5 pr-5 w-full gap-4">
                    <div class="w-full col-span-1">
                        <div>
                            <label class="label-text" for="textareaLabel">Observación</label>
                            <textarea disabled
                                class="textarea input w-full py-2 bg-base-200/50 text-base-content/60 border-base-content/20 disabled:bg-base-200/40 disabled:text-base-content/60 disabled:border-base-content/20"
                                placeholder="Sin Observación" id="textareaLabel">{{ trim($solicitud->observacion) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-actions justify-end mt-6 gap-2">
                    <a href="{{ route('solicitudes') }}"class="btn btn-secondary">Regresar</a>
                </div>
            @elseif ($solicitud->estado == 'Rechazada')
                <div class="flex justify-between items-center pt-5 pl-5">
                    <div class="alert alert-soft alert-error" role="alert">
                        <h3 class="text-lg font-semibold">La solicitud a sido {{ $solicitud->estado }} por le usuario
                            {{ $solicitud->contestada_por }}</h3>
                    </div>
                </div>
                <div class="grid grid-cols-1 mt-4 pl-5 pr-5 w-full gap-4">
                    <div class="w-full col-span-1">
                        <div>
                            <label class="label-text" for="textareaLabel">Observación</label>
                            <textarea disabled
                                class="textarea input w-full py-2 bg-base-200/50 text-base-content/60 border-base-content/20 disabled:bg-base-200/40 disabled:text-base-content/60 disabled:border-base-content/20"
                                placeholder="Sin Observación" id="textareaLabel">{{ trim($solicitud->observacion) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-actions justify-end mt-6 gap-2">
                    <a href="{{ route('solicitudes') }}"class="btn btn-secondary">Regresar</a>
                </div>
            @endif

        @endif
    </form>



</div>
