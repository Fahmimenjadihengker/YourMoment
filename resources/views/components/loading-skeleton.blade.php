@props([
    'lines' => 3,
    'avatar' => false,
    'card' => true,
])

<div {{ $attributes->merge(['class' => $card ? 'bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700' : '']) }}>
    <div class="animate-pulse flex {{ $avatar ? 'items-start gap-4' : 'flex-col gap-3' }}">
        @if($avatar)
            <!-- Avatar skeleton -->
            <div class="w-12 h-12 rounded-full bg-slate-200 dark:bg-slate-700 flex-shrink-0"></div>
        @endif
        
        <div class="flex-1 space-y-3">
            @for($i = 0; $i < $lines; $i++)
                <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded-lg {{ $i === $lines - 1 ? 'w-3/4' : 'w-full' }}"></div>
            @endfor
        </div>
    </div>
</div>
