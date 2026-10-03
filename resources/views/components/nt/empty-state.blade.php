@props(['icon' => 'fa-seedling', 'title', 'description' => null])

<div {{ $attributes->class(['flex flex-col items-center justify-center rounded-lg border border-dashed bg-surface px-6 py-12 text-center']) }}>
    <span class="mb-4 grid h-14 w-14 place-items-center rounded-full bg-primary/10 text-xl text-primary-strong">
        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
    </span>
    <h3 class="text-lg font-semibold">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 max-w-md text-sm text-muted-foreground">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-5 flex flex-wrap justify-center gap-3">{{ $slot }}</div>
    @endif
</div>
