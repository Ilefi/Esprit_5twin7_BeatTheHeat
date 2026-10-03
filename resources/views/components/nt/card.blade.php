{{-- Card with optional named slots: <x-slot:header>, <x-slot:footer>. --}}
@props(['padding' => true, 'hover' => false])

<div {{ $attributes->class(['nt-card', 'nt-card-hover' => $hover]) }}>
    @isset($header)
        <div {{ $header->attributes->class(['flex flex-wrap items-center justify-between gap-3 border-b px-5 py-4']) }}>
            {{ $header }}
        </div>
    @endisset

    <div @class(['p-5 sm:p-6' => $padding])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div {{ $footer->attributes->class(['flex flex-wrap items-center justify-end gap-3 rounded-b-lg border-t bg-muted/40 px-5 py-3']) }}>
            {{ $footer }}
        </div>
    @endisset
</div>
