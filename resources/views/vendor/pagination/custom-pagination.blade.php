@if ($paginator->hasPages())
    <div class="flex flex-wrap items-center justify-between gap-2 py-4 pt-6">
        <div class="me-2 block max-w-sm text-sm text-base-content/80 sm:mb-0">
            Mostrando
            <span class="font-semibold text-base-content/80">
                {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }}
            </span>
            de
            <span class="font-semibold">{{ $paginator->total() }}</span>
            registros
        </div>

        <nav class="join" role="navigation" aria-label="Pagination Navigation">
            {{-- Botón Anterior --}}
            @if ($paginator->onFirstPage())
                <button type="button" class="btn btn-soft btn-square join-item btn-disabled" aria-label="previous button">
                    <span class="icon-[tabler--chevron-left] size-5 rtl:rotate-180"></span>
                </button>
            @else
                <button
                    type="button"
                    wire:click="previousPage"
                    wire:loading.attr="disabled"
                    class="btn btn-soft btn-square join-item"
                    aria-label="previous button"
                >
                    <span class="icon-[tabler--chevron-left] size-5 rtl:rotate-180"></span>
                </button>
            @endif

            {{-- Números de página --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <button type="button" class="btn btn-soft btn-square join-item btn-disabled">
                        {{ $element }}
                    </button>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button
                                type="button"
                                class="btn btn-soft join-item btn-square text-bg-primary"
                                aria-current="page"
                            >
                                {{ $page }}
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="gotoPage({{ $page }})"
                                wire:loading.attr="disabled"
                                class="btn btn-soft join-item btn-square"
                            >
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Botón Siguiente --}}
            @if ($paginator->hasMorePages())
                <button
                    type="button"
                    wire:click="nextPage"
                    wire:loading.attr="disabled"
                    class="btn btn-soft btn-square join-item"
                    aria-label="next button"
                >
                    <span class="icon-[tabler--chevron-right] size-5 rtl:rotate-180"></span>
                </button>
            @else
                <button type="button" class="btn btn-soft btn-square join-item btn-disabled" aria-label="next button">
                    <span class="icon-[tabler--chevron-right] size-5 rtl:rotate-180"></span>
                </button>
            @endif
        </nav>
    </div>
@endif
