@props([
    'href' => null,
    'icon' => '+',
    'label' => '',
    'color' => 'emerald',
])

@php
    $colorClasses = [
        'emerald' => 'bg-gradient-to-br from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 shadow-emerald-500/30',
        'indigo' => 'bg-gradient-to-br from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 shadow-indigo-500/30',
        'red' => 'bg-gradient-to-br from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 shadow-red-500/30',
        'blue' => 'bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 shadow-blue-500/30',
    ];
    
    $classes = $colorClasses[$color] ?? $colorClasses['emerald'];
@endphp

@if($href)
    <a href="{{ $href }}" 
       {{ $attributes->merge(['class' => "fixed bottom-24 right-4 z-40 flex items-center justify-center w-14 h-14 rounded-2xl text-white shadow-xl active:scale-95 transition-all duration-200 touch-manipulation {$classes}"]) }}
       style="margin-bottom: env(safe-area-inset-bottom);">
        <span class="text-2xl">{{ $icon }}</span>
        @if($label)
            <span class="sr-only">{{ $label }}</span>
        @endif
    </a>
@else
    <button type="button"
            {{ $attributes->merge(['class' => "fixed bottom-24 right-4 z-40 flex items-center justify-center w-14 h-14 rounded-2xl text-white shadow-xl active:scale-95 transition-all duration-200 touch-manipulation {$classes}"]) }}
            style="margin-bottom: env(safe-area-inset-bottom);">
        <span class="text-2xl">{{ $icon }}</span>
        @if($label)
            <span class="sr-only">{{ $label }}</span>
        @endif
    </button>
@endif
