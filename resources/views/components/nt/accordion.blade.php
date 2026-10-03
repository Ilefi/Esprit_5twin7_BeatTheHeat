{{-- One item open at a time. Items: <x-nt.accordion-item id="q1" title="…">…</x-nt.accordion-item> --}}
@props(['open' => null])

<div x-data="{ open: @js($open) }" {{ $attributes->class(['divide-y rounded-lg border bg-surface']) }}>
    {{ $slot }}
</div>
