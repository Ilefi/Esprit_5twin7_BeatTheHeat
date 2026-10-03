{{-- Table wrapper. Slots: <x-slot:toolbar>, default = <thead>/<tbody>, <x-slot:footer> (pagination). --}}
@props(['title' => null, 'caption' => null])

<div {{ $attributes->class(['nt-card overflow-hidden']) }}>
    @if ($title || isset($toolbar))
        <div class="flex flex-col gap-3 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            @if ($title)
                <h2 class="text-base font-semibold">{{ $title }}</h2>
            @endif
            @isset($toolbar)
                <div class="flex flex-wrap items-center gap-2">{{ $toolbar }}</div>
            @endisset
        </div>
    @endif

    <div class="relative overflow-x-auto">
        <table class="nt-table">
            @if ($caption)
                <caption class="sr-only">{{ $caption }}</caption>
            @endif
            {{ $slot }}
        </table>
    </div>

    @isset($footer)
        <div class="border-t px-5 py-3">{{ $footer }}</div>
    @endisset
</div>
