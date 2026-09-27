@props(['field', 'label', 'rowspan' => null, 'sortField', 'sortDirection'])

<th
    class="font-semibold text-center cursor-pointer select-none hover:opacity-80"
    @if($rowspan) rowspan="{{ $rowspan }}" @endif
    wire:click="sortBy('{{ $field }}')"
    wire:loading.class="opacity-50"
>
    <span class="inline-flex items-center justify-center gap-1">
        {{ $label }}
        @if ($sortField === $field)
            <span class="icon-[tabler--chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}] size-3.5"></span>
        @else
            <span class="icon-[tabler--chevrons-up-down] size-3.5 opacity-30"></span>
        @endif
    </span>
</th>
