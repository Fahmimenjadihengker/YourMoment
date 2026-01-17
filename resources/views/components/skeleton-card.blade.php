{{-- Skeleton Loading State Card --}}
<div {{ $attributes->merge(['class' => 'rounded-2xl p-6 border shadow-lg relative overflow-hidden bg-gradient-to-br from-slate-100 to-slate-50 border-slate-200']) }}>
    <!-- Animated background pulse -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-pulse"></div>
    
    <!-- Content -->
    <div class="relative z-10 space-y-4">
        <!-- Header skeleton -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-200 animate-pulse"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-slate-200 rounded w-32 animate-pulse"></div>
                <div class="h-3 bg-slate-150 rounded w-24 animate-pulse"></div>
            </div>
        </div>

        <!-- Content lines skeleton -->
        <div class="space-y-3 pt-2">
            <div class="h-3 bg-slate-200 rounded w-full animate-pulse"></div>
            <div class="h-3 bg-slate-200 rounded w-5/6 animate-pulse"></div>
            <div class="h-3 bg-slate-200 rounded w-4/5 animate-pulse"></div>
        </div>
    </div>
</div>
