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
                <div class="w-24 h-24 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg class="w-12 h-12 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                    </svg>
                </div>
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
        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/serviceworker.js').catch(err => console.log('SW registration failed'));
        }
    </script>
</body>

</html>