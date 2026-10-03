{{--
    Display: <x-nt.rating-stars :rating="4.5" />
    Input:   <x-nt.rating-stars input name="rating" :value="old('rating')" label="Note globale" />  (keyboard: arrows)
--}}
@props(['rating' => 0, 'size' => 'sm', 'input' => false, 'name' => 'rating', 'value' => null, 'label' => 'Note'])

@php
    $sizes = ['xs' => 'text-xs', 'sm' => 'text-sm', 'md' => 'text-lg', 'lg' => 'text-2xl'];
    $sizeClass = $sizes[$size] ?? $sizes['sm'];
    $labels = [1 => 'Très mauvais', 2 => 'Décevant', 3 => 'Correct', 4 => 'Très bien', 5 => 'Excellent'];
@endphp

@if ($input)
    <div x-data="ntRatingInput(@js((int) $value))" {{ $attributes->class(['flex flex-wrap items-center gap-3']) }}>
        <input type="hidden" name="{{ $name }}" :value="value || ''">
        <div role="radiogroup" aria-label="{{ $label }}" class="flex items-center gap-1 {{ $sizeClass }}" x-on:keydown="onKey($event)" x-on:mouseleave="hover = 0">
            @foreach ($labels as $star => $starLabel)
                <button type="button" role="radio" data-star="{{ $star }}"
                        :aria-checked="(value === {{ $star }}).toString()"
                        :tabindex="(value === {{ $star }} || (value === 0 && {{ $star }} === 1)) ? 0 : -1"
                        aria-label="{{ $star }} sur 5 — {{ $starLabel }}"
                        x-on:click="select({{ $star }})" x-on:mouseenter="hover = {{ $star }}"
                        class="rounded p-0.5 transition hover:scale-110"
                        :class="shown >= {{ $star }} ? 'text-gold' : 'text-border'">
                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                </button>
            @endforeach
        </div>
        <span class="text-sm text-muted-foreground" x-text="(@js($labels))[shown] ?? 'Choisissez une note'" aria-live="polite"></span>
    </div>
@else
    <span {{ $attributes->class(['inline-flex items-center gap-0.5', $sizeClass]) }} role="img" aria-label="{{ number_format((float) $rating, 1, ',', ' ') }} sur 5">
        @for ($i = 1; $i <= 5; $i++)
            @if ($rating >= $i)
                <i class="fa-solid fa-star text-gold" aria-hidden="true"></i>
            @elseif ($rating >= $i - 0.5)
                <i class="fa-solid fa-star-half-stroke text-gold" aria-hidden="true"></i>
            @else
                <i class="fa-regular fa-star text-muted-foreground/50" aria-hidden="true"></i>
            @endif
        @endfor
    </span>
@endif
