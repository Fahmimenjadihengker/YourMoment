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
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="mb-3">
                <a href="{{ route('dashboard') }}" class="inline-block hover:opacity-80 transition">
                    @if(file_exists(public_path('images/logo_yourmoment.png')))
                        <img src="{{ asset('images/logo_yourmoment.png') }}" alt="YourMoment" class="h-32 w-auto">
                    @else
                        <div class="text-9xl">💚</div>
                    @endif
                </a>
            </div>

            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-slate-900">YourMoment</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola keuanganmu dengan cara yang mudah & menyenangkan</p>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        <script>
        @RegisterServiceWorkerScript
        </script>
    </body>
</html>
