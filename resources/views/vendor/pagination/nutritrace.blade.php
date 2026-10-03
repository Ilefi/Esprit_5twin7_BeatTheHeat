@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        @if (method_exists($paginator, 'total'))
            <p class="text-sm text-muted-foreground">
                Résultats <span class="font-semibold text-foreground">{{ $paginator->firstItem() }}</span>
                à <span class="font-semibold text-foreground">{{ $paginator->lastItem() }}</span>
                sur <span class="font-semibold text-foreground">{{ $paginator->total() }}</span>
            </p>
        @endif

        <ul class="flex items-center gap-1">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="nt-btn nt-btn-ghost nt-btn-sm opacity-50" aria-disabled="true" aria-label="Page précédente">
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="nt-btn nt-btn-ghost nt-btn-sm" aria-label="Page précédente">
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </a>
                @endif
            </li>

            @isset($elements)
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li><span class="px-2 text-muted-foreground">{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li>
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="nt-btn nt-btn-primary nt-btn-sm min-w-9">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="nt-btn nt-btn-ghost nt-btn-sm min-w-9" aria-label="Page {{ $page }}">{{ $page }}</a>
                                @endif
                            </li>
                        @endforeach
                    @endif
                @endforeach
            @endisset

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="nt-btn nt-btn-ghost nt-btn-sm" aria-label="Page suivante">
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </a>
                @else
                    <span class="nt-btn nt-btn-ghost nt-btn-sm opacity-50" aria-disabled="true" aria-label="Page suivante">
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
