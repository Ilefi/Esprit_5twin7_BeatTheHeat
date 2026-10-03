{{-- @include('partials.shared.breadcrumb', ['items' => [['label' => 'Produits', 'url' => route(...)], ['label' => 'Huile']]]) --}}
<nav aria-label="Fil d'Ariane" @class(['text-sm', $class ?? null])>
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-muted-foreground">
        @foreach ($items as $item)
            <li class="flex items-center gap-2">
                @if (! $loop->first)
                    <i class="fa-solid fa-chevron-right text-[0.6rem] opacity-60" aria-hidden="true"></i>
                @endif

                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}" class="hover:text-primary-strong hover:underline">
                        @if ($loop->first)<i class="fa-solid fa-house me-1 text-xs" aria-hidden="true"></i>@endif{{ $item['label'] }}
                    </a>
                @else
                    <span @if ($loop->last) aria-current="page" class="font-medium text-foreground" @endif>{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
