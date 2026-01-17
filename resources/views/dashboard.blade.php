<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Dashboard</span>
        </div>
    </x-slot>

    {{-- ==================== PERIOD SELECTOR ==================== --}}
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Ringkasan Keuangan</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $periodDetail ?? 'Periode saat ini' }}</p>
            </div>
            
            {{-- Period Toggle --}}
            <div class="inline-flex bg-slate-100 dark:bg-slate-700 rounded-xl p-1 gap-1">
                <a href="{{ route('dashboard', ['period' => 'weekly']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ ($periodMode ?? 'monthly') === 'weekly' ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Minggu Ini
                    </span>
                </a>
                <a href="{{ route('dashboard', ['period' => 'monthly']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ ($periodMode ?? 'monthly') === 'monthly' ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Bulan Ini
                    </span>
                </a>
            </div>
        </div>
    </div>

    {{-- ==================== TOP STATS ROW ==================== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
        {{-- Balance Card --}}
        <div class="xl:col-span-2 bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-600 rounded-2xl p-6 lg:p-8 shadow-xl relative overflow-hidden">
            <div class="absolute -top-20 -right-20 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            
            <div class="relative z-10">
                <p class="text-emerald-100 text-xs font-semibold uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Total Saldo
                </p>
                <p class="text-3xl lg:text-4xl xl:text-5xl font-black text-white mb-4">
                    Rp {{ number_format($totalBalance ?? 0, 0, ',', '.') }}
                </p>
                
                {{-- Quick Actions --}}
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('transactions.create-income') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur rounded-lg text-white text-sm font-semibold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        Pemasukan
                    </a>
                    <a href="{{ route('transactions.create-expense') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur rounded-lg text-white text-sm font-semibold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        Pengeluaran
                    </a>
                </div>
            </div>
        </div>

        {{-- Income Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-3">
                <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m0 0l-3-3m3 3l3-3"/></svg>
                <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded">{{ $currentMonth ?? 'Bulan Ini' }}</span>
            </div>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Pemasukan</p>
            <p class="text-2xl lg:text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                +Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}
            </p>
        </div>

        {{-- Expense Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-3">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V8m0 0l3 3m-3-3l-3 3"/></svg>
                <span class="text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 px-2 py-1 rounded">{{ $currentMonth ?? 'Bulan Ini' }}</span>
            </div>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Pengeluaran</p>
            <p class="text-2xl lg:text-3xl font-bold text-red-600 dark:text-red-400">
                −Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}
            </p>
        </div>
    </div>


    {{-- ==================== MAIN CONTENT GRID ==================== --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8">
        
        {{-- LEFT COLUMN: Transactions & Insight --}}
        <div class="xl:col-span-2 space-y-6">
            
            {{-- AI Financial Insight --}}
            @if(isset($financialInsight) && !empty($financialInsight['text']))
            <div class="bg-gradient-to-r from-indigo-50 via-white to-purple-50 dark:from-indigo-900/20 dark:via-slate-800 dark:to-purple-900/20 rounded-2xl p-6 border border-indigo-200 dark:border-indigo-800">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <h3 class="font-bold text-slate-900 dark:text-white">Insight Keuangan</h3>
                            <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                @if(($financialInsight['source'] ?? '') === 'ai')
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                    AI Analysis
                                @else
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    Based on Data
                                @endif
                            </span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed">{{ $financialInsight['text'] }}</p>
                        <a href="{{ route('ai-recommendation') }}" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 text-sm font-semibold mt-3 hover:underline">
                            Lihat Analisis Lengkap →
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Recent Transactions --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Transaksi Terbaru
                    </h3>
                    <a href="{{ route('transactions.index') }}" class="text-sm text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">
                        Lihat Semua →
                    </a>
                </div>

                @if(isset($recentTransactions) && $recentTransactions->count() > 0)
                    {{-- Desktop Table View --}}
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 dark:bg-slate-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Keterangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                @foreach($recentTransactions as $transaction)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xl">{{ $transaction->category->icon ?? '' }}</span>
                                            @if(!$transaction->category->icon)
                                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                            @endif
                                            <span class="font-medium text-slate-900 dark:text-white">{{ $transaction->category->name ?? 'Tanpa Kategori' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm">{{ $transaction->description ?? '—' }}</td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm">{{ $transaction->transaction_date?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right font-bold {{ $transaction->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $transaction->type === 'income' ? '+' : '−' }}Rp {{ number_format($transaction->amount ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile List View --}}
                    <div class="lg:hidden divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($recentTransactions as $transaction)
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="w-10 h-10 bg-slate-100 dark:bg-slate-700 rounded-xl flex items-center justify-center text-lg">
                                    @if($transaction->category->icon ?? null)
                                        {{ $transaction->category->icon }}
                                    @else
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900 dark:text-white text-sm truncate">{{ $transaction->category->name ?? 'Tanpa Kategori' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $transaction->transaction_date?->format('d M') ?? '-' }}</p>
                                </div>
                            </div>
                            <p class="font-bold text-sm {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $transaction->type === 'income' ? '+' : '−' }}Rp {{ number_format($transaction->amount ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 lg:p-12 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                        <p class="text-slate-900 dark:text-white font-semibold mb-2">Belum ada transaksi</p>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Mulai catat keuanganmu sekarang!</p>
                        <div class="flex gap-3 justify-center flex-wrap">
                            <a href="{{ route('transactions.create-income') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                Pemasukan
                            </a>
                            <a href="{{ route('transactions.create-expense') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                Pengeluaran
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN: Health Score, Target, Allowance --}}
        <div class="space-y-6">
            
            {{-- Health Score Card --}}
            @if(isset($healthScore) && isset($healthScore['score']))
                @php
                    $scoreColorClass = match($healthScore['color'] ?? 'gray') {
                        'green' => 'from-emerald-500 to-green-600',
                        'yellow' => 'from-yellow-400 to-amber-500',
                        'orange' => 'from-orange-400 to-red-500',
                        'red' => 'from-red-500 to-rose-600',
                        default => 'from-slate-400 to-slate-500',
                    };
                    $bgColorClass = match($healthScore['color'] ?? 'gray') {
                        'green' => 'from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border-emerald-200 dark:border-emerald-800',
                        'yellow' => 'from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 border-yellow-200 dark:border-yellow-800',
                        'orange' => 'from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 border-orange-200 dark:border-orange-800',
                        'red' => 'from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 border-red-200 dark:border-red-800',
                        default => 'from-slate-50 to-gray-50 dark:from-slate-800 dark:to-gray-800 border-slate-200 dark:border-slate-700',
                    };
                    $textColorClass = match($healthScore['color'] ?? 'gray') {
                        'green' => 'text-emerald-700 dark:text-emerald-400',
                        'yellow' => 'text-amber-700 dark:text-amber-400',
                        'orange' => 'text-orange-700 dark:text-orange-400',
                        'red' => 'text-red-700 dark:text-red-400',
                        default => 'text-slate-700 dark:text-slate-400',
                    };
                @endphp
                <div class="bg-gradient-to-br {{ $bgColorClass }} rounded-2xl p-6 border shadow-lg">
                    <div class="text-center">
                        <div class="flex items-center justify-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-rose-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Skor Kesehatan Keuangan</h3>
                        </div>

                        <div class="relative inline-flex items-center justify-center mb-4">
                            <div class="w-24 h-24 rounded-full bg-gradient-to-br {{ $scoreColorClass }} flex items-center justify-center shadow-lg">
                                <div class="w-20 h-20 rounded-full bg-white dark:bg-slate-800 flex items-center justify-center">
                                    <span class="text-3xl font-black {{ $textColorClass }}">{{ $healthScore['score'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="inline-block px-4 py-1.5 rounded-full bg-gradient-to-r {{ $scoreColorClass }} shadow-md mb-2">
                            <span class="text-white font-bold text-sm">{{ $healthScore['label'] ?? 'Unknown' }}</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">dari 100 poin maksimal</p>
                    </div>
                </div>
            @endif

            {{-- Saving Goal Progress --}}
            @if($walletSetting && $walletSetting->financial_goal)
                @php
                    $progress = $walletSetting->financial_goal > 0
                        ? min(100, (($totalBalance ?? 0) / $walletSetting->financial_goal) * 100)
                        : 0;
                @endphp
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                            Target Tabungan
                        </h3>
                        <a href="{{ route('savings.index') }}" class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">Detail →</a>
                    </div>
                    
                    <div class="mb-3">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-600 dark:text-slate-400">Progress</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ round($progress) }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-3 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between text-sm">
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 text-xs">Terkumpul</p>
                            <p class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($totalBalance ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-slate-500 dark:text-slate-400 text-xs">Target</p>
                            <p class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($walletSetting->financial_goal, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Budget/Allowance Info --}}
            @if($walletSetting && ($walletSetting->monthly_allowance || $walletSetting->weekly_allowance))
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Uang Jajan
                    </h3>
                    <div class="space-y-3">
                        @if($walletSetting->monthly_allowance)
                            <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                                <span class="text-sm text-blue-700 dark:text-blue-400 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Per Bulan
                                </span>
                                <span class="font-bold text-blue-700 dark:text-blue-400">Rp {{ number_format($walletSetting->monthly_allowance, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($walletSetting->weekly_allowance)
                            <div class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                                <span class="text-sm text-purple-700 dark:text-purple-400 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Per Minggu
                                </span>
                                <span class="font-bold text-purple-700 dark:text-purple-400">Rp {{ number_format($walletSetting->weekly_allowance, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- AI Assistant CTA --}}
            <a href="{{ route('ai.chat') }}" class="block bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-2xl p-6 shadow-lg transition group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white">AI Financial Assistant</h3>
                        <p class="text-indigo-200 text-sm">Tanya apa saja tentang keuanganmu</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
