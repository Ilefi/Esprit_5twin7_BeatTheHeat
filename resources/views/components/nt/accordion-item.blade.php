@props(['id', 'title'])

<div>
    <h3 class="text-base">
        <button type="button" id="acc-btn-{{ $id }}" aria-controls="acc-panel-{{ $id }}"
                x-on:click="open = open === @js($id) ? null : @js($id)" :aria-expanded="(open === @js($id)).toString()"
                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left font-heading font-semibold transition hover:text-primary-strong">
            <span>{{ $title }}</span>
            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-muted text-xs transition"
                  :class="open === @js($id) ? 'rotate-45 bg-primary text-primary-foreground' : ''" aria-hidden="true">
                <i class="fa-solid fa-plus"></i>
            </span>
        </button>
    </h3>
    <div id="acc-panel-{{ $id }}" role="region" aria-labelledby="acc-btn-{{ $id }}" x-show="open === @js($id)" x-cloak
         x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
         class="px-5 pb-5 text-muted-foreground">
        {{ $slot }}
    </div>
</div>
