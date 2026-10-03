{{-- A → E scale with the current grade highlighted. Class names are listed in full (no string building). --}}
@props(['grade', 'size' => 'md', 'showLabel' => false])

@php
    $grade = strtoupper((string) $grade);
    $active = [
        'A' => 'bg-eco-a text-primary-foreground',
        'B' => 'bg-eco-b text-foreground',
        'C' => 'bg-eco-c text-foreground',
        'D' => 'bg-eco-d text-foreground',
        'E' => 'bg-eco-e text-danger-foreground',
    ];
    $inactive = [
        'A' => 'bg-eco-a/20',
        'B' => 'bg-eco-b/20',
        'C' => 'bg-eco-c/25',
        'D' => 'bg-eco-d/20',
        'E' => 'bg-eco-e/20',
    ];
    $sizes = [
        'sm' => ['box' => 'h-5 w-4 text-[0.6rem]', 'current' => 'h-7 w-6 text-xs', 'wrap' => 'gap-0.5 p-0.5'],
        'md' => ['box' => 'h-7 w-6 text-xs', 'current' => 'h-9 w-8 text-sm', 'wrap' => 'gap-0.5 p-1'],
        'lg' => ['box' => 'h-10 w-9 text-sm', 'current' => 'h-14 w-12 text-2xl', 'wrap' => 'gap-1 p-1.5'],
    ][$size] ?? null;
    $sizes ??= ['box' => 'h-7 w-6 text-xs', 'current' => 'h-9 w-8 text-sm', 'wrap' => 'gap-0.5 p-1'];
    $label = \App\Support\EcoScore::grades()[$grade]['label'] ?? '';
@endphp

<div {{ $attributes->class(['inline-flex flex-col items-start gap-1']) }}>
    <div class="inline-flex items-center rounded-lg bg-surface shadow-sm ring-1 ring-border {{ $sizes['wrap'] }}"
         role="img" aria-label="Éco-score {{ $grade }} : {{ $label }}">
        @foreach (['A', 'B', 'C', 'D', 'E'] as $letter)
            <span aria-hidden="true" @class([
                'grid place-items-center rounded-md font-heading font-bold transition',
                $sizes['current'].' '.$active[$letter].' shadow-md' => $letter === $grade,
                $sizes['box'].' '.$inactive[$letter].' text-foreground/50' => $letter !== $grade,
            ])>{{ $letter }}</span>
        @endforeach
    </div>
    @if ($showLabel)
        <span class="text-xs font-medium text-muted-foreground">{{ $label }}</span>
    @endif
</div>
