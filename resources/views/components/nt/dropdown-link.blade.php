@props(['href' => null, 'icon' => null, 'danger' => false])

@php
    $classes = ['flex w-full items-center gap-3 px-4 py-2 text-left text-sm transition', $danger ? 'text-danger-strong hover:bg-danger/8' : 'text-foreground hover:bg-muted'];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
@else
    <button type="submit" {{ $attributes->class($classes) }}>
@endif
    @if ($icon)
        <i class="fa-solid {{ $icon }} w-4 text-center text-muted-foreground" aria-hidden="true"></i>
    @endif
    <span class="flex-1">{{ $slot }}</span>
@if ($href)
    </a>
@else
    </button>
@endif
