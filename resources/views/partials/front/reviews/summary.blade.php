{{-- Rating summary: average, count, distribution 5 → 1, sub-ratings. --}}
<div class="nt-card p-6">
    <div class="flex items-center gap-4">
        <p class="font-heading text-5xl font-extrabold">{{ number_format($stats->average, 1, ',', ' ') }}</p>
        <div>
            <x-nt.rating-stars :rating="$stats->average" size="md" />
            <p class="mt-1 text-sm text-muted-foreground">{{ $stats->count }} avis publiés</p>
        </div>
    </div>

    <ul class="mt-6 space-y-2" aria-label="Répartition des notes">
        @foreach ($stats->distribution as $star => $count)
            @php $percent = $stats->count ? round($count / $stats->count * 100) : 0; @endphp
            <li>
                <a href="{{ request()->fullUrlWithQuery(['note' => $star]) }}#avis" class="group flex items-center gap-3 text-sm">
                    <span class="w-10 shrink-0 font-medium">{{ $star }} <i class="fa-solid fa-star text-xs text-gold" aria-hidden="true"></i></span>
                    <span class="h-2.5 flex-1 overflow-hidden rounded-full bg-muted" aria-hidden="true">
                        <span class="block h-full rounded-full bg-gold transition-all group-hover:bg-gold-strong" style="width: {{ $percent }}%"></span>
                    </span>
                    <span class="w-10 shrink-0 text-right text-muted-foreground">{{ $count }}<span class="sr-only"> avis à {{ $star }} étoiles</span></span>
                </a>
            </li>
        @endforeach
    </ul>

    <dl class="mt-6 space-y-3 border-t pt-5 text-sm">
        @foreach (['Qualité' => $stats->quality, 'Transparence' => $stats->transparency, 'Rapport qualité/prix' => $stats->value] as $label => $value)
            <div class="flex items-center justify-between gap-3">
                <dt class="text-muted-foreground">{{ $label }}</dt>
                <dd class="flex items-center gap-2 font-semibold">
                    <span class="h-1.5 w-20 overflow-hidden rounded-full bg-muted" aria-hidden="true"><span class="block h-full bg-primary" style="width: {{ $value * 20 }}%"></span></span>
                    {{ number_format($value, 1, ',', ' ') }}
                </dd>
            </div>
        @endforeach
    </dl>
</div>
