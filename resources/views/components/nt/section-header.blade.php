@props(['eyebrow' => null, 'title', 'subtitle' => null, 'align' => 'center', 'tag' => 'h2'])

@php
    $alignments = ['center' => 'mx-auto max-w-2xl text-center', 'left' => 'max-w-2xl text-left'];
@endphp

<header {{ $attributes->class(['mb-10 lg:mb-14', $alignments[$align] ?? $alignments['center']]) }}>
    @if ($eyebrow)
        <p class="nt-eyebrow mb-3">{{ $eyebrow }}</p>
    @endif
    <{{ $tag }} class="text-3xl font-bold sm:text-4xl">{{ $title }}</{{ $tag }}>
    @if ($subtitle)
        <p class="mt-4 text-lg text-muted-foreground">{{ $subtitle }}</p>
    @endif
    {{ $slot }}
</header>
