{{-- Root of every layout: front, account, admin and auth all extend this file. --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    @include('partials.shared.head')
    @stack('styles')
</head>
<body class="@yield('body_class', 'min-h-screen bg-background text-foreground')">
    <a href="#contenu" class="skip-link">Aller au contenu principal</a>

    @yield('body')

    @include('partials.shared.toasts')
    @stack('scripts')
</body>
</html>
