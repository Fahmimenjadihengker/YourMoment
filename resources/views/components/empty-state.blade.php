{{-- Empty State Card --}}
@props([
    'icon' => null,
    'title' => 'Belum ada data',
    'description' => 'Mulai dengan menambahkan transaksi',
    'action' => null,
    'actionText' => 'Tambah Sekarang',
    'actionUrl' => '#'
])

<div {{ $attributes->merge(['class' => 'rounded-2xl p-8 sm:p-10 text-center border border-slate-300/50 bg-gradient-to-br from-slate-50 via-white to-slate-50 shadow-lg relative overflow-hidden']) }}>
    <!-- Decorative background -->
    <div class="absolute -bottom-16 -right-16 w-48 h-48 bg-slate-100 rounded-full blur-3xl opacity-30"></div>
    
    <div class="relative z-10 flex flex-col items-center justify-center max-w-sm mx-auto">
        <!-- Icon -->
        @if($icon)
            <div class="text-6xl sm:text-7xl mb-4 inline-block">{{ $icon }}</div>
        @else
            <div class="w-16 h-16 mb-4 rounded-full bg-slate-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        @endif

        <!-- Title -->
        <h3 class="font-bold text-slate-900 text-lg sm:text-xl mb-3">{{ $title }}</h3>

        <!-- Description -->
        @if($description)
        <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">{{ $description }}</p>
        @endif

        <!-- Action Button -->
        @if($action || $actionUrl !== '#')
        <a href="{{ $actionUrl }}" class="inline-flex items-center justify-center px-6 py-3 rounded-lg font-bold text-sm bg-gradient-to-r from-emerald-500 to-teal-600 text-white hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-md hover:shadow-lg active:scale-95">
            {{ $actionText }}
        </a>
        @endif
    </div>
</div>
