{{-- <x-nt.dropdown><x-slot:trigger>…</x-slot:trigger> links… </x-nt.dropdown> --}}
@props(['align' => 'right', 'width' => 'w-60', 'label' => 'Ouvrir le menu'])

@php
    $alignments = ['right' => 'right-0 origin-top-right', 'left' => 'left-0 origin-top-left'];
@endphp

<div class="relative" x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape="open = false; $refs.trigger.focus()">
    <button type="button" x-ref="trigger" x-on:click="open = ! open" :aria-expanded="open.toString()" aria-haspopup="true" aria-label="{{ $label }}"
            {{ $trigger->attributes->class(['flex items-center gap-2 rounded-lg']) }}>
        {{ $trigger }}
    </button>

    <div x-show="open" x-cloak
         x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
         x-transition:leave="transition duration-100 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         {{ $attributes->class(['absolute z-40 mt-2 overflow-hidden rounded-lg border bg-surface py-1 shadow-lg', $alignments[$align] ?? $alignments['right'], $width]) }}
         x-on:click="open = false">
        {{ $slot }}
    </div>
</div>
