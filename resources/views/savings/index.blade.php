<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span class="text-xl">🎯</span>
            <span>Target Tabungan</span>
        </div>
    </x-slot>

    {{-- Page Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">Target Tabungan</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola target keuangan dan wujudkan impianmu</p>
        </div>
        <a href="{{ route('savings.create') }}"
            class="hidden lg:inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Target Baru
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Saved -->
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl p-5 text-white shadow-lg">
            <p class="text-emerald-100 text-xs font-medium mb-1">💰 Total Terkumpul</p>
            <p class="text-2xl lg:text-3xl font-bold">Rp {{ number_format($totalSaved ?? 0, 0, ',', '.') }}</p>
        </div>
        
        <!-- Active Goals -->
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-5 text-white shadow-lg">
            <p class="text-indigo-100 text-xs font-medium mb-1">🎯 Target Aktif</p>
            <p class="text-2xl lg:text-3xl font-bold">{{ $activeCount ?? 0 }}</p>
        </div>

        <!-- Completed Goals -->
        <div class="bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl p-5 text-white shadow-lg">
            <p class="text-amber-100 text-xs font-medium mb-1">🏆 Tercapai</p>
            <p class="text-2xl lg:text-3xl font-bold">{{ $completedCount ?? 0 }}</p>
        </div>

        <!-- Total Target -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-lg border border-slate-200 dark:border-slate-700">
            <p class="text-slate-500 dark:text-slate-400 text-xs font-medium mb-1">📊 Total Target</p>
            <p class="text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">Rp {{ number_format($totalTarget ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Active Goals Section --}}
    <section class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>🔥</span> Target Aktif
            </h2>
        </div>

        @if(isset($activeGoals) && $activeGoals->isNotEmpty())
            {{-- Desktop: Grid Layout --}}
            <div class="hidden lg:grid lg:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($activeGoals as $goal)
                    <a href="{{ route('savings.show', $goal) }}" 
                       class="block bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-700 transition-all group">
                        <div class="flex items-start gap-4 mb-4">
                            <!-- Icon -->
                            <div class="flex-shrink-0 w-14 h-14 rounded-xl flex items-center justify-center text-2xl shadow-sm" 
                                 style="background-color: {{ $goal->color }}20;">
                                {{ $goal->icon }}
                            </div>

                            <!-- Title & Deadline -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition truncate">
                                        {{ $goal->name }}
                                    </h3>
                                    @if($goal->priority === 'high')
                                        <span class="flex-shrink-0 px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs font-semibold rounded">
                                            Prioritas
                                        </span>
                                    @endif
                                </div>
                                @if($goal->deadline)
                                    <p class="text-xs mt-1 {{ $goal->is_overdue ? 'text-red-500' : 'text-slate-500 dark:text-slate-400' }}">
                                        📅 {{ $goal->deadline->format('d M Y') }}
                                        @if(!$goal->is_overdue && $goal->days_remaining)
                                            <span class="text-slate-400">({{ $goal->days_remaining }} hari)</span>
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-3">
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="font-semibold" style="color: {{ $goal->color }};">{{ round($goal->progress) }}%</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-500" 
                                     style="width: {{ $goal->progress }}%; background-color: {{ $goal->color }};"></div>
                            </div>
                        </div>

                        <!-- Amount Info -->
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">
                                Rp {{ number_format($goal->current_amount, 0, ',', '.') }}
                            </span>
                            <span class="text-slate-400">→</span>
                            <span class="font-semibold text-slate-900 dark:text-white">
                                Rp {{ number_format($goal->target_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Mobile: List Layout --}}
            <div class="lg:hidden space-y-3">
                @foreach($activeGoals as $goal)
                    <a href="{{ route('savings.show', $goal) }}" 
                       class="block bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm active:scale-[0.99] transition-all">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center text-xl" 
                                 style="background-color: {{ $goal->color }}20;">
                                {{ $goal->icon }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div>
                                        <h3 class="font-bold text-slate-900 dark:text-white truncate">{{ $goal->name }}</h3>
                                        @if($goal->deadline)
                                            <p class="text-xs {{ $goal->is_overdue ? 'text-red-500' : 'text-slate-500' }}">
                                                📅 {{ $goal->deadline->format('d M Y') }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="font-bold text-sm" style="color: {{ $goal->color }};">{{ round($goal->progress) }}%</span>
                                </div>

                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden mb-2">
                                    <div class="h-2 rounded-full" style="width: {{ $goal->progress }}%; background-color: {{ $goal->color }};"></div>
                                </div>

                                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                    <span>Rp {{ number_format($goal->current_amount, 0, ',', '.') }}</span>
                                    <span>Rp {{ number_format($goal->target_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <svg class="w-5 h-5 text-slate-400 flex-shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl p-8 lg:p-12 text-center border border-slate-200 dark:border-slate-700">
                <p class="text-4xl mb-3">🎯</p>
                <p class="text-slate-900 dark:text-white font-semibold text-lg mb-2">Belum Ada Target</p>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Mulai buat target tabunganmu dan wujudkan impianmu!</p>
                <a href="{{ route('savings.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Target Pertama
                </a>
            </div>
        @endif
    </section>

    {{-- Completed Goals Section --}}
    @if(isset($completedGoals) && $completedGoals->isNotEmpty())
        <section>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <span>🏆</span> Target Tercapai
                <span class="text-sm font-normal text-slate-500">({{ $completedCount }})</span>
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($completedGoals as $goal)
                    <div class="bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-xl p-4 border border-emerald-200 dark:border-emerald-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-500 flex items-center justify-center text-white text-lg shadow-sm">
                                ✓
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-emerald-800 dark:text-emerald-300 truncate">{{ $goal->name }}</h3>
                                <p class="text-sm text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($goal->target_amount, 0, ',', '.') }} tercapai! 🎉
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Mobile FAB --}}
    <div class="lg:hidden fixed bottom-20 right-4 z-40">
        <a href="{{ route('savings.create') }}" 
           class="flex items-center justify-center w-14 h-14 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full shadow-lg shadow-emerald-500/30 active:scale-95 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </a>
    </div>
</x-app-layout>
