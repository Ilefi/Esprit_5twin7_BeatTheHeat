{{--
    Accessible modal (focus trap, Esc, backdrop click).
    Open:  x-on:click="$dispatch('open-modal', 'name')"   Close: $dispatch('close-modal')
--}}
@props(['name', 'title' => null, 'show' => false, 'maxWidth' => 'lg'])

@php
    $widths = ['sm' => 'sm:max-w-sm', 'md' => 'sm:max-w-md', 'lg' => 'sm:max-w-lg', 'xl' => 'sm:max-w-xl', '2xl' => 'sm:max-w-2xl'];
@endphp

<div x-data="ntModal(@js($name), @js((bool) $show))"
     x-on:open-modal.window="openIf($event.detail)"
     x-on:close-modal.window="open = false"
     x-on:keydown.escape.window="open = false"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
     role="dialog" aria-modal="true" @if ($title) aria-labelledby="modal-{{ $name }}-title" @endif>
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-foreground/50 backdrop-blur-sm" x-on:click="open = false"></div>

    <div x-ref="panel" x-show="open" x-on:keydown.tab="trap($event)"
         x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         {{ $attributes->class(['relative max-h-[90vh] w-full overflow-y-auto rounded-xl border bg-surface shadow-lg', $widths[$maxWidth] ?? $widths['lg']]) }}>
        @if ($title)
            <div class="flex items-center justify-between gap-4 border-b px-5 py-4">
                <h2 id="modal-{{ $name }}-title" class="text-lg font-semibold">{{ $title }}</h2>
                <button type="button" x-on:click="open = false" class="nt-btn nt-btn-ghost nt-btn-icon" aria-label="Fermer la fenêtre">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        <div class="px-5 py-5">{{ $slot }}</div>

        @isset($footer)
            <div class="flex flex-wrap justify-end gap-3 border-t bg-muted/40 px-5 py-3">{{ $footer }}</div>
        @endisset
    </div>
</div>
