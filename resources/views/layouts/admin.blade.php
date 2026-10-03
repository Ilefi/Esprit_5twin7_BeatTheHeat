{{-- Back office: sidebar + topbar + breadcrumb + page header. --}}
@extends('layouts.master')

@section('body_class', 'min-h-screen bg-background text-foreground')

@section('body')
    <div x-data="{
            sidebar: false,
            collapsed: false,
            init() {
                // Remember the collapsed sidebar per browser (storage may be unavailable).
                try { this.collapsed = localStorage.getItem('nt-sidebar') === '1'; } catch (e) {}
                this.$watch('collapsed', (value) => { try { localStorage.setItem('nt-sidebar', value ? '1' : '0'); } catch (e) {} });
            },
         }"
         x-on:keydown.escape.window="sidebar = false"
         class="flex min-h-screen">
        @include('partials.admin.sidebar')

        <div class="flex min-w-0 flex-1 flex-col">
            @include('partials.admin.topbar')

            <main id="contenu" tabindex="-1" class="flex-1 px-4 py-6 focus:outline-none sm:px-6 lg:px-8 lg:py-8">
                @hasSection('breadcrumb')
                    <div class="mb-4">@yield('breadcrumb')</div>
                @endif

                @include('partials.admin.page-header')

                @include('partials.shared.flash')

                <div class="mt-6">
                    @yield('content')
                </div>
            </main>

            <footer class="border-t px-4 py-4 text-xs text-muted-foreground sm:px-6 lg:px-8">
                NutriTrace · Back office — données de démonstration
            </footer>
        </div>
    </div>
@endsection
