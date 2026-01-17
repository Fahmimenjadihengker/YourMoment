{{-- Tooltip Badge Component
    Displays a metric with an informational tooltip
    Props:
    - label: Main text/label
    - tooltip: Hover text with explanation
    - icon: Optional emoji or icon
    - value: Optional value to display
    - valueClass: CSS classes for value styling
--}}

@props([
    'label' => '',
    'tooltip' => '',
    'icon' => '',
    'value' => '',
    'valueClass' => 'text-slate-700'
])

<div class="inline-flex items-center gap-1 group cursor-help">
    @if($icon)
    <span class="text-lg">{{ $icon }}</span>
    @endif
    
    <span class="font-semibold {{ $valueClass }}">{{ $label }}</span>
    
    @if($tooltip)
    <span class="text-xs opacity-50 ml-0.5">?</span>
    
    {{-- Tooltip --}}
    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-slate-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50 shadow-lg">
        {{ $tooltip }}
        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
    </div>
    @endif
    
    @if($value)
    <span class="text-sm font-bold {{ $valueClass }}">{{ $value }}</span>
    @endif
</div>
