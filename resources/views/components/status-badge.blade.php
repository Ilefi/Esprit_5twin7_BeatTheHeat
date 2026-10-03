<span {{ $attributes->class([$classes]) }}>
    @if ($icon)
        <i class="fa-solid {{ $iconClass }} text-[0.7em]" aria-hidden="true"></i>
    @endif
    {{ $label }}
</span>
