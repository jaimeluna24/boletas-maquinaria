@props([
    'show' => false,
    'maxWidth' => 'md',
])

@php
$maxWidthClass = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
][$maxWidth] ?? 'max-w-md';
@endphp

@if ($show)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
        {{-- Fondo oscuro --}}
        <div
            class="fixed inset-0 bg-black/50"
            wire:click="cerrarModal"
        ></div>

        {{-- Contenido del modal --}}
        <div class="relative z-10 w-full {{ $maxWidthClass }} mx-4 my-8 rounded-lg bg-base-100 shadow-xl">
            {{ $slot }}
        </div>
    </div>
@endif
