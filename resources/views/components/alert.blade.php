<div
    x-data="{
        alertas: [],
        agregar(detalle) {
            const id = Date.now() + Math.random();
            this.alertas.push({
                id,
                type: detalle.type ?? 'info',
                title: detalle.title ?? null,
                message: detalle.message ?? '',
                autoClose: detalle.autoClose ?? true,
                duration: detalle.duration ?? 5000,
            });

            if (detalle.autoClose !== false) {
                setTimeout(() => this.quitar(id), detalle.duration ?? 5000);
            }
        },
        quitar(id) {
            this.alertas = this.alertas.filter(a => a.id !== id);
        }
    }"
    @notificar.window="agregar($event.detail)"
    class="fixed bottom-4 right-4 z-100 flex flex-col gap-2 w-full max-w-md"
>
    <template x-for="alerta in alertas" :key="alerta.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4"
            class="alert flex items-center gap-4"
            :class="{
                'alert-success': alerta.type === 'success',
                'alert-warning': alerta.type === 'warning',
                'alert-error': alerta.type === 'error',
                'alert-info': alerta.type === 'info',
            }"
            role="alert"
        >
            <span
                class="shrink-0 size-6"
                :class="{
                    'icon-[tabler--circle-check]': alerta.type === 'success',
                    'icon-[tabler--alert-triangle]': alerta.type === 'warning',
                    'icon-[tabler--circle-x]': alerta.type === 'error',
                    'icon-[tabler--info-circle]': alerta.type === 'info',
                }"
            ></span>

            <p class="flex-1">
                <template x-if="alerta.title">
                    <span class="text-lg font-semibold" x-text="alerta.title + ' '"></span>
                </template>
                <span x-text="alerta.message"></span>
            </p>

            <button
                type="button"
                @click="quitar(alerta.id)"
                class="btn btn-text btn-circle btn-sm shrink-0"
                aria-label="Cerrar"
            >
                <span class="icon-[tabler--x] size-4"></span>
            </button>
        </div>
    </template>
</div>
