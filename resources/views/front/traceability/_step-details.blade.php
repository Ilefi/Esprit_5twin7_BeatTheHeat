{{-- One lot step's details — shared by the desktop stepper and the mobile timeline. --}}
<div class="mt-2 space-y-1.5 text-sm text-foreground/85">
    @if ($step->actor)
        <p><i class="fa-solid fa-user-tie me-1.5 w-3.5 text-muted-foreground" aria-hidden="true"></i><a href="{{ route('front.actors.show', $step->actor->slug) }}" class="nt-link">{{ $step->actor->name }}</a></p>
    @endif
    <p><i class="fa-solid fa-location-dot me-1.5 w-3.5 text-muted-foreground" aria-hidden="true"></i>{{ $step->location }}</p>
    <p><i class="fa-regular fa-calendar me-1.5 w-3.5 text-muted-foreground" aria-hidden="true"></i><time datetime="{{ $step->date->toDateString() }}">{{ $step->date->translatedFormat('d M Y') }}</time></p>
    <p class="pt-1 text-muted-foreground">{{ $step->action }}</p>
</div>
@if (count($step->documents))
    <ul class="mt-3 flex flex-wrap gap-1.5" aria-label="Justificatifs">
        @foreach ($step->documents as $document)
            <li class="nt-badge whitespace-normal"><i class="fa-solid fa-file-lines text-[0.7em]" aria-hidden="true"></i>{{ $document }}</li>
        @endforeach
    </ul>
@endif
<div class="mt-auto pt-3">
    @if ($step->verified)
        <x-nt.badge variant="success" icon="fa-circle-check">Vérifié</x-nt.badge>
    @elseif ($step->stage !== 'consumer')
        <x-nt.badge variant="warning" icon="fa-hourglass-half">En attente de vérification</x-nt.badge>
    @endif
</div>
