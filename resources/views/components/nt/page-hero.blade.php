{{-- Front-office page header with breadcrumb. Default slot = actions, <x-slot:aside> = right column. --}}
@props(['title', 'subtitle' => null, 'eyebrow' => null, 'breadcrumb' => []])

<section {{ $attributes->class(['nt-gradient-hero relative overflow-hidden border-b']) }}>
    <div class="nt-pattern" aria-hidden="true"></div>
    <div class="nt-container relative py-10 lg:py-14">
        @if ($breadcrumb)
            @include('partials.shared.breadcrumb', ['items' => array_merge([['label' => 'Accueil', 'url' => route('front.home')]], $breadcrumb), 'class' => 'mb-6'])
        @endif

        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                @if ($eyebrow)
                    <p class="nt-eyebrow mb-3">{{ $eyebrow }}</p>
                @endif
                <h1 class="text-3xl font-extrabold sm:text-4xl lg:text-5xl">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-4 text-lg text-muted-foreground">{{ $subtitle }}</p>
                @endif
                @if ($slot->isNotEmpty())
                    <div class="mt-6 flex flex-wrap gap-3">{{ $slot }}</div>
                @endif
            </div>
            @isset($aside)
                <div class="shrink-0">{{ $aside }}</div>
            @endisset
        </div>
    </div>
</section>
