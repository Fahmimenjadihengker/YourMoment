{{-- Empty State Card --}}
@props([
    'icon' => '📊',
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
        <div class="text-6xl sm:text-7xl mb-4 inline-block">{{ $icon }}</div>

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
