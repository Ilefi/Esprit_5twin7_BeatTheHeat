{{-- GET filter form. Put x-nt.form.* fields in the default slot. --}}
@props(['action', 'resetUrl' => null])

<form method="GET" action="{{ $action }}" role="search" {{ $attributes->class(['nt-card flex flex-col gap-3 p-4 sm:flex-row sm:flex-wrap sm:items-end']) }}>
    {{ $slot }}
    <div class="flex gap-2 sm:ms-auto">
        <x-nt.button type="submit" icon="fa-filter" size="sm">Filtrer</x-nt.button>
        @if (request()->query())
            <x-nt.button variant="ghost" size="sm" :href="$resetUrl ?? $action" icon="fa-rotate-left">Réinitialiser</x-nt.button>
        @endif
    </div>
</form>
