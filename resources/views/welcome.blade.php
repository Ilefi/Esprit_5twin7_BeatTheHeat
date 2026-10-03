<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col items-center justify-center bg-background p-6 font-sans text-foreground">
        <main class="w-full max-w-md rounded-lg border border-border bg-surface p-8 text-center">
            <h1 class="text-2xl font-semibold text-primary">NutriTrace</h1>
            <p class="mt-2 text-sm text-muted">Coming soon.</p>
        </main>
    </body>
</html>
