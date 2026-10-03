@props(['actor'])

@php
    $icons = [
        'producer' => ['fa-tractor', 'bg-primary/12 text-primary-strong'],
        'processor' => ['fa-industry', 'bg-earth/12 text-earth'],
        'distributor' => ['fa-store', 'bg-info/12 text-info-strong'],
    ];
    [$icon, $tint] = $icons[$actor->type] ?? ['fa-building', 'bg-muted text-muted-foreground'];
@endphp

<article {{ $attributes->class(['nt-card nt-card-hover relative flex flex-col p-5']) }}>
    <div class="flex items-start gap-4">
        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-lg text-lg {{ $tint }}">
            <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
        </span>
        <div class="min-w-0">
            <h3 class="font-semibold leading-snug">
                <a href="{{ route('front.actors.show', $actor->slug) }}" class="after:absolute after:inset-0 hover:text-primary-strong">{{ $actor->name }}</a>
            </h3>
            <p class="text-sm text-muted-foreground"><i class="fa-solid fa-location-dot me-1 text-xs" aria-hidden="true"></i>{{ $actor->city }}, {{ $actor->region }}</p>
        </div>
        @if ($actor->verified)
            <span class="ms-auto text-primary" title="Acteur vérifié"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span class="sr-only">Acteur vérifié</span></span>
        @endif
    </div>
    <p class="mt-3 line-clamp-2 text-sm text-muted-foreground">{{ $actor->description }}</p>
    <div class="mt-auto flex flex-wrap items-center gap-2 pt-4">
        <x-status-badge type="actor_type" :value="$actor->type" />
        @foreach ($actor->certifications as $certification)
            <x-nt.cert-badge :certification="$certification" />
        @endforeach
    </div>
</article>
