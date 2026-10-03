{{--
    Accessible tabs. :tabs = ['key' => 'Label'] or ['key' => ['label' => 'Avis', 'icon' => 'fa-star', 'count' => 12]]
    Panels: <x-nt.tab-panel name="key">…</x-nt.tab-panel>
--}}
@props(['tabs', 'active' => null])

@php
    $tabs = collect($tabs)->map(fn ($tab) => is_array($tab) ? $tab : ['label' => $tab]);
    $active ??= $tabs->keys()->first();
@endphp

<div x-data="{
        tab: @js($active),
        init() {
            this.fromHash(false);
            window.addEventListener('hashchange', () => this.fromHash(true));
        },
        fromHash(scroll) {
            const hash = window.location.hash.slice(1);
            if (! hash || ! this.$el.querySelector('#tab-' + CSS.escape(hash))) return;
            this.tab = hash;
            if (scroll || document.readyState !== 'complete') this.$nextTick(() => this.$refs.list.scrollIntoView({ block: 'start' }));
        },
        move(step) {
            const tabs = [...this.$refs.list.querySelectorAll('[role=tab]')];
            const next = tabs[(tabs.indexOf(document.activeElement) + step + tabs.length) % tabs.length];
            next.focus();
            next.click();
        },
    }" {{ $attributes }}>
    <div class="relative -mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
        <div role="tablist" aria-label="Sections" x-ref="list" class="flex min-w-max scroll-mt-24 gap-1 border-b"
             x-on:keydown.right.prevent="move(1)" x-on:keydown.left.prevent="move(-1)">
            @foreach ($tabs as $key => $tab)
                <button type="button" role="tab" id="tab-{{ $key }}" aria-controls="panel-{{ $key }}"
                        x-on:click="tab = @js($key); history.replaceState(null, '', '#' + @js($key))"
                        :aria-selected="(tab === @js($key)).toString()" :tabindex="tab === @js($key) ? 0 : -1"
                        class="relative -mb-px flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition"
                        :class="tab === @js($key) ? 'border-primary text-primary-strong' : 'border-transparent text-muted-foreground hover:text-foreground'">
                    @isset($tab['icon'])
                        <i class="fa-solid {{ $tab['icon'] }}" aria-hidden="true"></i>
                    @endisset
                    {{ $tab['label'] }}
                    @isset($tab['count'])
                        <span class="nt-badge px-2 py-0">{{ $tab['count'] }}</span>
                    @endisset
                </button>
            @endforeach
        </div>
    </div>

    <div class="pt-6">{{ $slot }}</div>
</div>
