<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Gavelisten') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            body {
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
            }
        </style>
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen flex items-center justify-center p-6 antialiased" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 1.5rem; text-align: center;">
        <main class="max-w-xl text-center">
            <p class="text-xl sm:text-2xl font-medium leading-relaxed" style="font-size: 1.25rem; line-height: 1.75; font-weight: 500;">
                Oops - gavelisten.app er lige gået i stykker. Nikolaj arbejder i døgndrift (eller noget) på at fixe det! 😁
            </p>
        </main>
    </body>
</html>
