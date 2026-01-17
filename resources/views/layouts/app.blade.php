<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="themeManager()" 
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" :content="darkMode ? '#1e293b' : '#10b981'">

    <title>{{ config('app.name', 'YourMoment') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Prevent flash of wrong theme + Theme Manager -->
    <script>
        (function() {
            const stored = localStorage.getItem('theme');
            if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
        
        // Theme Manager - must be defined before Alpine starts
        window.themeManager = function() {
            return {
                darkMode: localStorage.getItem('theme') === 'dark' || 
                         (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                sidebarOpen: true,
                
                init() {
                    // Watch for changes and persist
                    this.$watch('darkMode', (val) => {
                        localStorage.setItem('theme', val ? 'dark' : 'light');
                        document.querySelector('meta[name="theme-color"]')?.setAttribute('content', val ? '#1e293b' : '#10b981');
                    });
                }
            }
        }
    </script>

    <!-- PWA Configuration -->
    @PwaHead

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Design System Variables */
        :root {
            --color-primary: #10b981;
            --color-primary-dark: #059669;
            --color-secondary: #6366f1;
            --color-danger: #ef4444;
            --color-warning: #f59e0b;
            --color-success: #22c55e;
            --sidebar-width: 260px;
            --header-height: 64px;
        }
        
        /* Smooth scrolling */
        html { scroll-behavior: smooth; }
        
        /* Custom scrollbar for desktop */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-track { background: #1e293b; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        
        /* Mobile touch optimization */
        @media (max-width: 1023px) {
            body { overscroll-behavior-y: contain; }
        }
        
        /* Navigation links - ensure immediate response */
        nav a, aside a {
            touch-action: manipulation;
            -webkit-touch-callout: none;
        }
        
        /* Safe area for mobile */
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom, 0); }
        .pb-safe { padding-bottom: calc(70px + env(safe-area-inset-bottom, 0)); }
        
        /* Sidebar transition */
        .sidebar-transition { transition: width 0.2s ease, margin-left 0.2s ease; }
        
        /* Page animation */
        .page-enter { animation: fadeInUp 0.25s ease-out; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-100 dark:bg-slate-900">
    <div class="min-h-screen flex">
        
        <!-- ==================== DESKTOP SIDEBAR ==================== -->
        <aside class="hidden lg:flex lg:flex-col fixed inset-y-0 left-0 z-40 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 shadow-sm sidebar-transition"
               :class="sidebarOpen ? 'w-[260px]' : 'w-[72px]'">
            
            <!-- Logo -->
            <div class="h-16 flex items-center px-4 border-b border-slate-200 dark:border-slate-700">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden">
                    @if(file_exists(public_path('images/logo_yourmoment.png')))
                        <img src="{{ asset('images/logo_yourmoment.png') }}" alt="YourMoment" class="h-9 w-auto flex-shrink-0">
                    @else
                        <div class="w-9 h-9 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="text-white text-lg font-bold">Y</span>
                        </div>
                    @endif
                    <span class="font-bold text-slate-900 dark:text-white text-lg whitespace-nowrap" x-show="sidebarOpen" x-cloak>YourMoment</span>
                </a>
            </div>
            
            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                @php
                    $navItems = [
                        ['route' => 'dashboard', 'icon' => 'chart-bar', 'label' => 'Dashboard', 'match' => 'dashboard'],
                        ['route' => 'transactions.index', 'icon' => 'credit-card', 'label' => 'Transaksi', 'match' => 'transactions.*'],
                        ['route' => 'savings.index', 'icon' => 'flag', 'label' => 'Target Tabungan', 'match' => 'savings.*'],
                        ['route' => 'ai.chat', 'icon' => 'sparkles', 'label' => 'AI Assistant', 'match' => 'ai.*'],
                        ['route' => 'ai-recommendation', 'icon' => 'chart-pie', 'label' => 'Analisis Keuangan', 'match' => 'ai-recommendation'],
                    ];
                @endphp
                
                <p class="px-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2" x-show="sidebarOpen" x-cloak>Menu Utama</p>
                
                @foreach($navItems as $item)
                    @if(Route::has($item['route']))
                        <a href="{{ route($item['route']) }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs($item['match']) 
                                      ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 shadow-sm' 
                                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50' }}"
                           :class="!sidebarOpen && 'justify-center'"
                           :title="!sidebarOpen && '{{ $item['label'] }}'">
                            <span class="w-5 h-5 flex-shrink-0">
                                @switch($item['icon'])
                                    @case('chart-bar')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                        @break
                                    @case('credit-card')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        @break
                                    @case('flag')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                        @break
                                    @case('sparkles')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                        @break
                                    @case('chart-pie')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                                        @break
                                @endswitch
                            </span>
                            <span x-show="sidebarOpen" x-cloak class="whitespace-nowrap">{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
                
                <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-700">
                    <p class="px-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2" x-show="sidebarOpen" x-cloak>Pengaturan</p>
                    
                    <a href="{{ route('profile.edit') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('profile.*') 
                                  ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 shadow-sm' 
                                  : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50' }}"
                       :class="!sidebarOpen && 'justify-center'"
                       :title="!sidebarOpen && 'Profil & Pengaturan'">
                        <span class="w-5 h-5 flex-shrink-0">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        <span x-show="sidebarOpen" x-cloak class="whitespace-nowrap">Profil & Pengaturan</span>
                    </a>
                </div>
            </nav>
            
            <!-- User Section -->
            <div class="p-3 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3 px-3 py-2" :class="!sidebarOpen && 'justify-center'">
                    <div class="w-9 h-9 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div x-show="sidebarOpen" x-cloak class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('logout') }}" class="mt-2" x-show="sidebarOpen" x-cloak>
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                        <span class="text-xl">🚪</span>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ==================== MAIN CONTENT AREA ==================== -->
        <div class="flex-1 flex flex-col sidebar-transition" :class="sidebarOpen ? 'lg:ml-[260px]' : 'lg:ml-[72px]'">
            
            <!-- Desktop Top Bar -->
            <header class="hidden lg:flex h-16 items-center justify-between px-6 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <!-- Sidebar Toggle -->
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    
                    <!-- Page Title from header slot -->
                    @isset($header)
                        <div class="text-slate-900 dark:text-white font-semibold">{{ $header }}</div>
                    @endisset
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Toggle Dark Mode">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>
                    
                    <!-- Quick Add Transaction -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Transaksi</span>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 py-2 z-50">
                            <a href="{{ route('transactions.create-income') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                                <span class="text-lg">📥</span> Pemasukan
                            </a>
                            <a href="{{ route('transactions.create-expense') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                                <span class="text-lg">📤</span> Pengeluaran
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Mobile Header -->
            <header class="lg:hidden sticky top-0 z-30 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 px-4 py-3">
                <div class="flex items-center justify-between">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        @if(file_exists(public_path('images/logo_yourmoment.png')))
                            <img src="{{ asset('images/logo_yourmoment.png') }}" alt="YourMoment" class="h-8 w-auto">
                        @else
                            <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold">Y</span>
                            </div>
                        @endif
                        <span class="font-bold text-slate-900 dark:text-white">YourMoment</span>
                    </a>
                    
                    <div class="flex items-center gap-2">
                        <button @click="darkMode = !darkMode" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                            <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                            <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-6 xl:p-8 pb-safe lg:pb-8 page-enter">
                <div class="max-w-7xl mx-auto">
                    @hasSection('content')
                        @yield('content')
                    @else
                        {{ $slot }}
                    @endif
                </div>
            </main>
        </div>

        <!-- ==================== MOBILE BOTTOM NAVIGATION ==================== -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 safe-area-bottom">
            <div class="flex items-center justify-around h-16">
                @php
                    $mobileNav = [
                        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Home', 'match' => 'dashboard'],
                        ['route' => 'transactions.index', 'icon' => 'credit-card', 'label' => 'Transaksi', 'match' => 'transactions.*'],
                        ['route' => 'savings.index', 'icon' => 'flag', 'label' => 'Target', 'match' => 'savings.*'],
                        ['route' => 'ai.chat', 'icon' => 'sparkles', 'label' => 'AI', 'match' => 'ai.*'],
                        ['route' => 'profile.edit', 'icon' => 'user', 'label' => 'Profil', 'match' => 'profile.*'],
                    ];
                @endphp
                
                @foreach($mobileNav as $item)
                    @if(Route::has($item['route']))
                        <a href="{{ route($item['route']) }}" 
                           class="flex flex-col items-center justify-center flex-1 py-2 transition
                                  {{ request()->routeIs($item['match']) 
                                      ? 'text-emerald-600 dark:text-emerald-400' 
                                      : 'text-slate-400 dark:text-slate-500' }}">
                            <span class="w-5 h-5 mb-0.5">
                                @switch($item['icon'])
                                    @case('home')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        @break
                                    @case('credit-card')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        @break
                                    @case('flag')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                        @break
                                    @case('sparkles')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                        @break
                                    @case('user')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        @break
                                @endswitch
                            </span>
                            <span class="text-[10px] font-medium">{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </nav>
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container" 
         x-data="{ toasts: [] }"
         @toast.window="toasts.push({id: Date.now(), message: $event.detail.message, type: $event.detail.type || 'success'}); setTimeout(() => toasts.shift(), 4000)"
         class="fixed top-4 right-4 lg:right-8 lg:w-96 z-[100] flex flex-col gap-2 pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 :class="{
                     'bg-emerald-600': toast.type === 'success',
                     'bg-red-600': toast.type === 'error',
                     'bg-amber-500': toast.type === 'warning',
                     'bg-blue-600': toast.type === 'info'
                 }"
                 class="px-4 py-3 rounded-xl text-white text-sm font-medium shadow-lg pointer-events-auto flex items-center gap-3">
                <!-- Toast Icon -->
                <template x-if="toast.type === 'success'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <template x-if="toast.type === 'warning'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </template>
                <template x-if="toast.type === 'info'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>

    <script>
    // Service Worker Registration
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/serviceworker.js').catch(err => console.log('SW registration failed'));
    }
    
    // Toast Notification Helper (for minor notifications)
    window.showToast = (message, type = 'success') => {
        window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } }));
    };

    // SweetAlert Helper Functions
    window.showSwal = (options) => {
        const isDark = document.documentElement.classList.contains('dark');
        return Swal.fire({
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#f1f5f9' : '#1e293b',
            confirmButtonColor: '#10b981',
            ...options
        });
    };

    window.showSuccess = (title, text) => {
        return window.showSwal({
            icon: 'success',
            title: title,
            text: text,
            timer: 2500,
            showConfirmButton: false
        });
    };

    window.showError = (title, text) => {
        return window.showSwal({
            icon: 'error',
            title: title,
            text: text
        });
    };

    window.confirmDelete = (title, text) => {
        const isDark = document.documentElement.classList.contains('dark');
        return Swal.fire({
            icon: 'warning',
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: isDark ? '#475569' : '#94a3b8',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#f1f5f9' : '#1e293b',
            reverseButtons: true
        });
    };
    </script>

    {{-- SweetAlert Session Handler --}}
    @if(session('swal'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const swalData = @json(session('swal'));
            window.showSwal({
                icon: swalData.type || 'success',
                title: swalData.title || '',
                text: swalData.text || '',
                timer: swalData.timer || (swalData.type === 'success' ? 2500 : null),
                showConfirmButton: swalData.type !== 'success'
            });
        });
    </script>
    @endif

    {{-- Legacy success/error session handler (backwards compatible) --}}
    @if(session('success') && !session('swal'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.showSuccess('Berhasil', '{{ session('success') }}');
        });
    </script>
    @endif
    @if(session('error') && !session('swal'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.showError('Gagal', '{{ session('error') }}');
        });
    </script>
    @endif

    @stack('scripts')
</body>
</html>
