{{-- Reads the page's sections: page_title, page_subtitle, page_actions. --}}
<div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="min-w-0">
        <h1 class="text-2xl font-bold sm:text-3xl">@yield('page_title', 'Tableau de bord')</h1>
        @hasSection('page_subtitle')
            <p class="mt-1 text-muted-foreground">@yield('page_subtitle')</p>
        @endif
    </div>
    @hasSection('page_actions')
        <div class="flex flex-wrap gap-2">@yield('page_actions')</div>
    @endif
</div>
