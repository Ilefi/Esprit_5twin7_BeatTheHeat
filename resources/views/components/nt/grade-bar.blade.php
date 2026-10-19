{{-- Stacked A → E bar from a distribution ['A' => 12, 'B' => 3, …]. Class names are listed in full. --}}
@props(['distribution', 'label' => 'Répartition'])

@php
    $bars = ['A' => 'bg-eco-a', 'B' => 'bg-eco-b', 'C' => 'bg-eco-c', 'D' => 'bg-eco-d', 'E' => 'bg-eco-e'];
    $total = array_sum($distribution);
@endphp

@if ($total)
    <div {{ $attributes->class(['flex h-2 overflow-hidden rounded-full bg-muted']) }} role="img"
         aria-label="{{ $label }} : {{ collect($bars)->keys()->map(fn ($g) => $g.' '.round(($distribution[$g] ?? 0) / $total * 100).' %')->implode(', ') }}">
        @foreach ($bars as $grade => $bar)
            @if ($distribution[$grade] ?? 0)
                <span class="{{ $bar }}" style="width: {{ round($distribution[$grade] / $total * 100, 2) }}%"></span>
            @endif
        @endforeach
    </div>
@endif
