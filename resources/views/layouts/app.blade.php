<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- PWA Configuration -->
        @PwaHead

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-gradient-to-r from-white via-slate-50 to-white border-b-2 border-slate-200 ring-1 ring-black/5">
                    <div class="max-w-6xl mx-auto py-6 sm:py-8 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content with Layered Sections -->
            <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8">
                <div class="max-w-6xl mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <script>
        @RegisterServiceWorkerScript
        </script>

    </body>
</html>
