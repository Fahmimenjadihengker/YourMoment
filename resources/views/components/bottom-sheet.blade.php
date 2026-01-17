@props([
    'name' => 'modal',
    'maxWidth' => 'full',
    'title' => '',
    'showClose' => true,
])

@php
    $maxWidthClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        'full' => 'max-w-full',
    ];
@endphp

<div x-data="{ open: false }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
     x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
     x-on:keydown.escape.window="open = false"
     {{ $attributes }}>
    
    <!-- Trigger slot -->
    <div @click.stop="open = true" class="cursor-pointer">
        {{ $trigger ?? '' }}
    </div>

    <!-- Modal Backdrop -->
    <template x-teleport="body">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="open = false">
        </div>

        <!-- Bottom Sheet -->
        <div x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed inset-x-0 bottom-0 z-50 {{ $maxWidthClasses[$maxWidth] }} mx-auto"
             @click.outside="open = false">
            
            <div class="bg-white dark:bg-slate-800 rounded-t-3xl shadow-2xl overflow-hidden safe-area-bottom">
                <!-- Handle Bar -->
                <div class="flex justify-center pt-3 pb-2">
                    <div class="w-10 h-1 bg-slate-300 dark:bg-slate-600 rounded-full"></div>
                </div>

                <!-- Header -->
                @if($title || $showClose)
                    <div class="flex items-center justify-between px-5 pb-3">
                        @if($title)
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
                        @else
                            <div></div>
                        @endif
                        
                        @if($showClose)
                            <button @click="open = false" 
                                    class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif

                <!-- Content -->
                <div class="px-5 pb-5 max-h-[70vh] overflow-y-auto hide-scrollbar">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </template>
</div>

<style>
    .safe-area-bottom {
        padding-bottom: env(safe-area-inset-bottom);
    }
</style>
