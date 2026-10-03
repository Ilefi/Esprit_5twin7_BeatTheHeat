{{-- Public site: navbar + main + footer. Level 2 of the inheritance chain (master → front → account). --}}
@extends('layouts.master')

@section('body')
    @include('partials.front.navbar')

    <main id="contenu" tabindex="-1" class="focus:outline-none">
        @yield('hero')

        @includeWhen(session()->hasAny(['success', 'error', 'warning', 'info']), 'partials.front.flash-container')

        @yield('content')
    </main>

    {{-- Pages can replace this block or extend it with @parent (see front/home). --}}
    @section('pre_footer')
        @include('partials.front.newsletter')
    @show

    @include('partials.front.footer')
@endsection
