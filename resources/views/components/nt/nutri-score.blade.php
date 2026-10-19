{{-- A → E grade from Open Food Facts (Nutri-Score, Green-Score), same layout and tokens as x-nt.eco-score. --}}
@props(['grade', 'label' => 'Nutri-Score'])

@php
    $grade = strtoupper((string) $grade);
    $active = [
        'A' => 'bg-eco-a text-primary-foreground',
        'B' => 'bg-eco-b text-foreground',
        'C' => 'bg-eco-c text-foreground',
        'D' => 'bg-eco-d text-foreground',
        'E' => 'bg-eco-e text-danger-foreground',
    ];
@endphp

@if (array_key_exists($grade, $active))
    <div {{ $attributes->class(['inline-flex items-center gap-0.5 rounded-lg bg-surface p-1 shadow-sm ring-1 ring-border']) }} role="img" aria-label="{{ $label }} {{ $grade }}">
        @foreach ($active as $letter => $classes)
            <span aria-hidden="true" @class([
                'grid place-items-center rounded-md font-heading font-bold',
                'h-9 w-8 text-sm shadow-md '.$classes => $letter === $grade,
                'h-7 w-6 text-xs bg-muted text-foreground/50' => $letter !== $grade,
            ])>{{ $letter }}</span>
        @endforeach
    </div>
@endif
