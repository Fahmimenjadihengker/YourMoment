<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Analisis Keuangan</span>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- ==================== HEADER & MODE SELECTOR ==================== --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">
                    @if(($periodMode ?? 'monthly') === 'simulation')
                        Simulasi Keuangan
                    @else
                        Analisis Keuangan
                    @endif
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                    @if(($periodMode ?? 'monthly') === 'simulation')
                        Mode simulasi - data input manual
                    @else
                        {{ $period['detail'] ?? 'Periode saat ini' }}
                    @endif
                </p>
            </div>
            
            {{-- 3-Mode Selector --}}
            <div class="inline-flex bg-slate-100 dark:bg-slate-700 rounded-xl p-1 gap-1">
                <a href="{{ route('ai-recommendation', ['period' => 'monthly']) }}" 
                   class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ ($periodMode ?? 'monthly') === 'monthly' ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Bulanan
                    </span>
                </a>
                <a href="{{ route('ai-recommendation', ['period' => 'weekly']) }}" 
                   class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ ($periodMode ?? 'monthly') === 'weekly' ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Mingguan
                    </span>
                </a>
                <a href="{{ route('ai-recommendation', ['period' => 'simulation']) }}" 
                   class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ ($periodMode ?? 'monthly') === 'simulation' ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        Simulasi
                    </span>
                </a>
            </div>
        </div>

        {{-- ==================== SIMULATION FORM ==================== --}}
        @if(($periodMode ?? 'monthly') === 'simulation')
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-gradient-to-r from-indigo-500 to-purple-500">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    Mode Simulasi
                </h3>
                <p class="text-indigo-100 text-sm mt-1">Masukkan data keuangan untuk melihat analisis tanpa mempengaruhi data asli</p>
            </div>
            <div class="p-6">
                <form action="{{ route('ai-recommendation.simulate') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Income Input --}}
                        <div>
                            <label for="sim_income" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Pemasukan Periode
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input type="number" name="income" id="sim_income" 
                                       value="{{ old('income', $simulationInput['income'] ?? '') }}"
                                       class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                       placeholder="5000000" min="0" required>
                            </div>
                            @error('income')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Expense Input --}}
                        <div>
                            <label for="sim_expense" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Pengeluaran Sudah Terjadi
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input type="number" name="expense" id="sim_expense" 
                                       value="{{ old('expense', $simulationInput['expense'] ?? '') }}"
                                       class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                       placeholder="2000000" min="0" required>
                            </div>
                            @error('expense')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Days Remaining Input --}}
                        <div>
                            <label for="sim_days_remaining" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Sisa Hari Periode
                            </label>
                            <input type="number" name="days_remaining" id="sim_days_remaining" 
                                   value="{{ old('days_remaining', $simulationInput['days_remaining'] ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="15" min="1" max="365" required>
                            @error('days_remaining')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Total Days Input --}}
                        <div>
                            <label for="sim_total_days" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Total Hari Periode <span class="text-slate-400">(opsional)</span>
                            </label>
                            <input type="number" name="total_days" id="sim_total_days" 
                                   value="{{ old('total_days', $simulationInput['total_days'] ?? 30) }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="30" min="1" max="365">
                            @error('total_days')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-2.5 px-6 rounded-xl transition shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            Jalankan Simulasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- ==================== WARNINGS SECTION ==================== --}}
        @if(!empty($warnings))
        <div class="space-y-3">
            @foreach($warnings as $warning)
                @php
                    $warnClass = match($warning['level']) {
                        'critical' => 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-800',
                        'warning' => 'bg-amber-50 dark:bg-amber-900/20 border-amber-300 dark:border-amber-800',
                        default => 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-800',
                    };
                    $warnIconClass = match($warning['level']) {
                        'critical' => 'text-red-500',
                        'warning' => 'text-amber-500',
                        default => 'text-blue-500',
                    };
                    $warnTitleClass = match($warning['level']) {
                        'critical' => 'text-red-800 dark:text-red-300',
                        'warning' => 'text-amber-800 dark:text-amber-300',
                        default => 'text-blue-800 dark:text-blue-300',
                    };
                @endphp
                <div class="border rounded-xl p-4 {{ $warnClass }}">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            @if($warning['level'] === 'critical')
                                <svg class="w-5 h-5 {{ $warnIconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            @elseif($warning['level'] === 'warning')
                                <svg class="w-5 h-5 {{ $warnIconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                                <svg class="w-5 h-5 {{ $warnIconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold {{ $warnTitleClass }}">{{ $warning['title'] }}</h4>
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-1">{{ $warning['message'] }}</p>
                            @if(isset($warning['action']))
                                <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 font-medium">{{ $warning['action'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        {{-- ==================== RINGKASAN KONDISI KEUANGAN (FOUNDATION) ==================== --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @if(($periodMode ?? 'monthly') === 'simulation')
                        Hasil Simulasi
                    @else
                        Ringkasan Kondisi Keuangan
                    @endif
                </h3>
            </div>
            <div class="p-6">
                @if(($periodMode ?? 'monthly') === 'simulation')
                {{-- SIMULATION MODE: Only show period data, no total balance --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pemasukan Simulasi --}}
                    <div class="text-center p-5 bg-gradient-to-br from-emerald-600 to-teal-600 rounded-xl text-white">
                        <p class="text-emerald-100 text-sm font-medium mb-1">Pemasukan Periode</p>
                        <p class="text-2xl lg:text-3xl font-bold">Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}</p>
                        <p class="text-emerald-100 text-xs mt-2">Data input simulasi</p>
                    </div>
                    
                    {{-- Pengeluaran Simulasi --}}
                    <div class="text-center p-5 bg-gradient-to-br from-red-600 to-rose-600 rounded-xl text-white">
                        <p class="text-red-100 text-sm font-medium mb-1">Pengeluaran Sudah Terjadi</p>
                        <p class="text-2xl lg:text-3xl font-bold">Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}</p>
                        <p class="text-red-100 text-xs mt-2">Data input simulasi</p>
                    </div>
                    
                    {{-- Sisa Dana Periode --}}
                    @php
                        $sisaDana = max(0, ($totalIncome ?? 0) - ($totalExpense ?? 0));
                    @endphp
                    <div class="text-center p-5 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl text-white">
                        <p class="text-indigo-100 text-sm font-medium mb-1">Sisa Dana Periode</p>
                        <p class="text-2xl lg:text-3xl font-bold">Rp {{ number_format($sisaDana, 0, ',', '.') }}</p>
                        <p class="text-indigo-100 text-xs mt-2">Pemasukan - Pengeluaran</p>
                    </div>
                </div>
                @else
                {{-- NORMAL MODE: Show total balance (visual only) + period data --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Total Saldo - VISUAL ONLY --}}
                    <div class="text-center p-5 bg-gradient-to-br from-emerald-600 to-teal-600 rounded-xl text-white relative">
                        <p class="text-emerald-100 text-sm font-medium mb-1">Total Saldo</p>
                        <p class="text-2xl lg:text-3xl font-bold">Rp {{ number_format($totalBalance ?? 0, 0, ',', '.') }}</p>
                        <p class="text-emerald-100 text-xs mt-2">Referensi visual saja</p>
                    </div>
                    
                    {{-- Pemasukan --}}
                    <div class="text-center p-5 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Pemasukan Periode Ini</p>
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">+Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}</p>
                        @if(isset($trend['income']) && ($trend['has_previous_data'] ?? false))
                            <p class="text-xs mt-2 {{ $trend['income']['direction'] === 'up' ? 'text-emerald-600' : ($trend['income']['direction'] === 'down' ? 'text-red-600' : 'text-slate-500') }}">
                                {{ $trend['income']['direction'] === 'up' ? '+' : '' }}{{ $trend['income']['change_percent'] }}% dari periode lalu
                            </p>
                        @endif
                    </div>
                    
                    {{-- Pengeluaran --}}
                    <div class="text-center p-5 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Pengeluaran Periode Ini</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">-Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}</p>
                        @if(isset($trend['expense']) && ($trend['has_previous_data'] ?? false))
                            <p class="text-xs mt-2 {{ $trend['expense']['direction'] === 'down' ? 'text-emerald-600' : ($trend['expense']['direction'] === 'up' ? 'text-red-600' : 'text-slate-500') }}">
                                {{ $trend['expense']['direction'] === 'up' ? '+' : '' }}{{ $trend['expense']['change_percent'] }}% dari periode lalu
                            </p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ==================== MAIN CONTENT GRID ==================== --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- LEFT COLUMN (2/3) --}}
            <div class="xl:col-span-2 space-y-6">
                
                {{-- REKOMENDASI PENGELUARAN HARIAN (CORE FEATURE) --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Rekomendasi Pengeluaran Harian
                        </h3>
                    </div>
                    <div class="p-6">
                        @php
                            $recDaily = $dailyRecommendation['recommended_daily'] ?? 0;
                            $currentAvg = $dailyRecommendation['current_daily_avg'] ?? 0;
                            $remainingBudget = $dailyRecommendation['remaining_budget'] ?? 0;
                            $daysRemaining = $dailyRecommendation['days_remaining'] ?? 0;
                            $status = $dailyRecommendation['status'] ?? 'no_data';
                            $statusLabel = $dailyRecommendation['status_label'] ?? '-';
                            
                            // Fixed color classes for status
                            $statusBgClass = match($status) {
                                'under_budget' => 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800',
                                'on_track' => 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800',
                                'over_budget' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                                default => 'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-600',
                            };
                            $statusTextClass = match($status) {
                                'under_budget' => 'text-emerald-600 dark:text-emerald-400',
                                'on_track' => 'text-indigo-600 dark:text-indigo-400',
                                'over_budget' => 'text-red-600 dark:text-red-400',
                                default => 'text-slate-600 dark:text-slate-400',
                            };
                        @endphp
                        
                        {{-- Main Recommendation --}}
                        <div class="text-center mb-6 p-6 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl text-white">
                            <p class="text-indigo-100 text-sm mb-2">Agar saldo tetap aman, disarankan:</p>
                            <p class="text-4xl font-bold mb-1">Rp {{ number_format($recDaily, 0, ',', '.') }}</p>
                            <p class="text-indigo-200 text-sm">maksimal per hari</p>
                            <div class="mt-4 pt-4 border-t border-white/20 flex justify-center gap-6 text-sm">
                                <div>
                                    <p class="text-indigo-200">Sisa Anggaran</p>
                                    <p class="font-bold">Rp {{ number_format($remainingBudget, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-indigo-200">Sisa Hari</p>
                                    <p class="font-bold">{{ $daysRemaining }} hari</p>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Current Status --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600">
                                <p class="text-slate-500 dark:text-slate-400 text-sm mb-1">Rata-rata Harianmu Saat Ini</p>
                                <p class="text-xl font-bold text-slate-900 dark:text-white">Rp {{ number_format($currentAvg, 0, ',', '.') }}</p>
                            </div>
                            <div class="p-4 rounded-xl border {{ $statusBgClass }}">
                                <p class="text-slate-500 dark:text-slate-400 text-sm mb-1">Status</p>
                                <p class="text-xl font-bold {{ $statusTextClass }}">{{ $statusLabel }}</p>
                            </div>
                        </div>
                        
                        {{-- Category Recommendations --}}
                        @if(!empty($dailyRecommendation['category_recommendations']))
                        <div>
                            <h4 class="font-semibold text-slate-900 dark:text-white mb-4">Rekomendasi per Kategori</h4>
                            <div class="space-y-3">
                                @foreach($dailyRecommendation['category_recommendations'] as $catRec)
                                    @php
                                        // Fixed color classes for category status
                                        $catTextClass = match($catRec['status']) {
                                            'under' => 'text-emerald-600 dark:text-emerald-400',
                                            'ok' => 'text-indigo-600 dark:text-indigo-400',
                                            'over' => 'text-red-600 dark:text-red-400',
                                            default => 'text-slate-600 dark:text-slate-400',
                                        };
                                    @endphp
                                    <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600">
                                        <div class="flex items-center gap-3">
                                            <span class="text-lg">{{ $catRec['icon'] }}</span>
                                            <div>
                                                <p class="font-medium text-slate-900 dark:text-white">{{ $catRec['name'] }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $catRec['percentage'] }}% dari total</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold {{ $catTextClass }}">Rp {{ number_format($catRec['recommended_daily'], 0, ',', '.') }}/hari</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Aktual: Rp {{ number_format($catRec['actual_daily'], 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- INSIGHT KEUANGAN --}}
                @if(!empty($insights))
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            Insight dan Analisis
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        @foreach($insights as $insight)
                            @php
                                $bgClass = match($insight['type']) {
                                    'positive' => 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800',
                                    'warning' => 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800',
                                    'danger' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                                    default => 'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-600',
                                };
                                $iconClass = match($insight['type']) {
                                    'positive' => 'text-emerald-500',
                                    'warning' => 'text-amber-500',
                                    'danger' => 'text-red-500',
                                    default => 'text-slate-500',
                                };
                            @endphp
                            <div class="p-4 rounded-xl border {{ $bgClass }}">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-0.5">
                                        @if($insight['icon'] === 'check-circle')
                                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @elseif($insight['icon'] === 'x-circle')
                                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @elseif($insight['icon'] === 'exclamation-circle')
                                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @elseif($insight['icon'] === 'trending-up')
                                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                        @elseif($insight['icon'] === 'trending-down')
                                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                                        @elseif($insight['icon'] === 'arrow-up')
                                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        @elseif($insight['icon'] === 'arrow-down')
                                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        @elseif($insight['icon'] === 'chart-pie')
                                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
                                        @elseif($insight['icon'] === 'info-circle')
                                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @else
                                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-slate-900 dark:text-white text-sm">{{ $insight['title'] }}</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $insight['text'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- POLA PENGELUARAN HARIAN --}}
                @if(isset($dailyPatterns) && ($dailyPatterns['average_daily'] ?? 0) > 0)
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                            Pola Pengeluaran Harian
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="text-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                                <p class="text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider mb-1">Rata-rata</p>
                                <p class="text-lg font-bold text-slate-900 dark:text-white">Rp {{ number_format($dailyPatterns['average_daily'], 0, ',', '.') }}</p>
                            </div>
                            <div class="text-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                                <p class="text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider mb-1">Tertinggi</p>
                                <p class="text-lg font-bold text-red-600 dark:text-red-400">Rp {{ number_format($dailyPatterns['max_daily'], 0, ',', '.') }}</p>
                            </div>
                            <div class="text-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                                <p class="text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider mb-1">Terendah</p>
                                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($dailyPatterns['min_daily'], 0, ',', '.') }}</p>
                            </div>
                            <div class="text-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                                <p class="text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider mb-1">Konsistensi</p>
                                @php
                                    $consistencyLabel = match($dailyPatterns['spending_consistency']) {
                                        'stable' => 'Stabil',
                                        'moderate' => 'Sedang',
                                        'fluctuating' => 'Fluktuatif',
                                        default => '-',
                                    };
                                    // Fixed color class for consistency
                                    $consistencyClass = match($dailyPatterns['spending_consistency']) {
                                        'stable' => 'text-emerald-600 dark:text-emerald-400',
                                        'moderate' => 'text-amber-600 dark:text-amber-400',
                                        'fluctuating' => 'text-red-600 dark:text-red-400',
                                        default => 'text-slate-600 dark:text-slate-400',
                                    };
                                @endphp
                                <p class="text-lg font-bold {{ $consistencyClass }}">{{ $consistencyLabel }}</p>
                            </div>
                        </div>
                        
                        @if($dailyPatterns['max_day'])
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Pengeluaran tertinggi terjadi pada <span class="font-semibold">{{ $dailyPatterns['max_day'] }}</span>
                        </p>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- RIGHT COLUMN (1/3) --}}
            <div class="space-y-6">
                {{-- SKOR KESEHATAN KEUANGAN --}}
                @php
                    $score = $healthScore['score'] ?? 0;
                    $scoreLabel = $healthScore['label'] ?? 'N/A';
                    $rawColor = $healthScore['color'] ?? 'gray';
                    
                    // Determine status level for visual hierarchy
                    $statusLevel = 'neutral';
                    if ($score >= 75) {
                        $statusLevel = 'healthy';
                    } elseif ($score >= 50) {
                        $statusLevel = 'warning';
                    } else {
                        $statusLevel = 'danger';
                    }
                    
                    // Fixed color classes (not dynamic) for Tailwind compilation
                    $circleColorClass = match($statusLevel) {
                        'healthy' => 'text-emerald-500',
                        'warning' => 'text-amber-500',
                        'danger' => 'text-red-500',
                        default => 'text-slate-500',
                    };
                    
                    $badgeBgClass = match($statusLevel) {
                        'healthy' => 'bg-emerald-100 dark:bg-emerald-900/30',
                        'warning' => 'bg-amber-100 dark:bg-amber-900/30',
                        'danger' => 'bg-red-100 dark:bg-red-900/30',
                        default => 'bg-slate-100 dark:bg-slate-700',
                    };
                    
                    $badgeTextClass = match($statusLevel) {
                        'healthy' => 'text-emerald-700 dark:text-emerald-400',
                        'warning' => 'text-amber-700 dark:text-amber-400',
                        'danger' => 'text-red-700 dark:text-red-400',
                        default => 'text-slate-700 dark:text-slate-400',
                    };
                    
                    $headerIconClass = match($statusLevel) {
                        'healthy' => 'text-emerald-500',
                        'warning' => 'text-amber-500',
                        'danger' => 'text-red-500',
                        default => 'text-slate-500',
                    };
                    
                    // Calculate circle progress (clamp between 0-100)
                    $scorePercent = max(0, min(100, $score));
                    $circumference = 351.86;
                    $dashArray = ($circumference * $scorePercent / 100) . ' ' . $circumference;
                @endphp
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 {{ $headerIconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Skor Kesehatan Keuangan
                        </h3>
                    </div>
                    <div class="p-6">
                        {{-- Score Circle with proper calculation --}}
                        <div class="flex justify-center mb-6">
                            <div class="relative w-36 h-36">
                                <svg class="w-36 h-36 transform -rotate-90" viewBox="0 0 128 128">
                                    {{-- Background circle --}}
                                    <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="10" fill="none" class="text-slate-200 dark:text-slate-700"/>
                                    {{-- Progress circle --}}
                                    <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="10" fill="none" 
                                            class="{{ $circleColorClass }}"
                                            stroke-dasharray="{{ $dashArray }}"
                                            stroke-linecap="round"
                                            style="transition: stroke-dasharray 0.5s ease-in-out;"/>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-4xl font-bold text-slate-900 dark:text-white">{{ $score }}</span>
                                    <span class="text-sm text-slate-500 dark:text-slate-400 font-medium">/100</span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Status Badge --}}
                        <div class="text-center mb-4">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold {{ $badgeBgClass }} {{ $badgeTextClass }}">
                                @if($statusLevel === 'healthy')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @elseif($statusLevel === 'warning')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                                {{ $scoreLabel }}
                            </span>
                        </div>
                        
                        {{-- Status Description --}}
                        <p class="text-center text-sm text-slate-600 dark:text-slate-400 mb-6">
                            @if($statusLevel === 'healthy')
                                Kondisi keuangan Anda dalam keadaan baik. Pertahankan pola pengeluaran ini.
                            @elseif($statusLevel === 'warning')
                                Perlu perhatian lebih pada pengeluaran Anda. Tinjau kategori pengeluaran terbesar.
                            @else
                                Kondisi keuangan memerlukan tindakan segera. Kurangi pengeluaran tidak penting.
                            @endif
                        </p>
                        
                        {{-- Score Breakdown with FIXED progress bars --}}
                        @if(isset($healthScore['breakdown']) && !empty($healthScore['breakdown']))
                        <div class="pt-5 border-t border-slate-200 dark:border-slate-700 space-y-4">
                            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rincian Skor</h4>
                            @foreach($healthScore['breakdown'] as $key => $item)
                                @php
                                    $itemScore = $item['score'] ?? 0;
                                    $itemMax = max($item['max'] ?? 1, 1);
                                    // Calculate percentage with clamp (min 2% for visibility, max 100%)
                                    $itemPercent = ($itemScore / $itemMax) * 100;
                                    $itemPercent = max(2, min(100, $itemPercent));
                                    
                                    // Determine item color based on its own ratio
                                    $itemRatio = $itemScore / $itemMax;
                                    $itemColorClass = 'bg-red-500';
                                    if ($itemRatio >= 0.7) {
                                        $itemColorClass = 'bg-emerald-500';
                                    } elseif ($itemRatio >= 0.4) {
                                        $itemColorClass = 'bg-amber-500';
                                    }
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $item['description'] }}</span>
                                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $itemScore }}/{{ $itemMax }}</span>
                                    </div>
                                    <div class="h-2.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full {{ $itemColorClass }} rounded-full transition-all duration-500 ease-out" 
                                             style="width: {{ $itemPercent }}%; min-width: 4px;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                {{-- KOMPOSISI PENGELUARAN --}}
                @if(!empty($topCategories))
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
                            Komposisi Pengeluaran
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        @foreach($topCategories as $category)
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">{{ $category['icon'] }}</span>
                                        <span class="font-medium text-slate-800 dark:text-slate-200 text-sm">{{ $category['name'] }}</span>
                                    </div>
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $category['percentage'] }}%</span>
                                </div>
                                <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500"
                                         style="width: {{ min($category['percentage'], 100) }}%; background-color: {{ $category['color'] }}"></div>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 text-right">
                                    Rp {{ number_format($category['total'], 0, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- QUICK ACTIONS --}}
                <div class="space-y-3">
                    <a href="{{ route('ai.chat') }}" 
                       class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        Tanya AI Assistant
                    </a>
                    <a href="{{ route('dashboard') }}"
                       class="w-full flex items-center justify-center gap-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold py-3 px-6 rounded-xl transition border border-slate-200 dark:border-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Kembali ke Dashboard
                    </a>
                    <a href="{{ route('transactions.index') }}"
                       class="w-full flex items-center justify-center gap-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold py-3 px-6 rounded-xl transition border border-slate-200 dark:border-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Lihat Transaksi
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
