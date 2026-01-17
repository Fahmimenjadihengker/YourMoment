<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span class="text-xl">📊</span>
            <span>Dashboard</span>
        </div>
    </x-slot>

    {{-- ==================== TOP STATS ROW ==================== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
        {{-- Balance Card --}}
        <div class="xl:col-span-2 bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-600 rounded-2xl p-6 lg:p-8 shadow-xl relative overflow-hidden">
            <div class="absolute -top-20 -right-20 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            
            <div class="relative z-10">
                <p class="text-emerald-100 text-xs font-semibold uppercase tracking-wider mb-2">💰 Total Saldo</p>
                <p class="text-3xl lg:text-4xl xl:text-5xl font-black text-white mb-4">
                    Rp {{ number_format($totalBalance ?? 0, 0, ',', '.') }}
                </p>
                
                {{-- Quick Actions --}}
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('transactions.create-income') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur rounded-lg text-white text-sm font-semibold transition">
                        <span>📥</span> Pemasukan
                    </a>
                    <a href="{{ route('transactions.create-expense') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur rounded-lg text-white text-sm font-semibold transition">
                        <span>📤</span> Pengeluaran
                    </a>
                </div>
            </div>
        </div>

        {{-- Income Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-3">
                <span class="text-2xl">📥</span>
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
                <span class="text-2xl">📤</span>
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
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white text-xl shadow-lg flex-shrink-0">
                        💡
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <h3 class="font-bold text-slate-900 dark:text-white">Insight Keuangan</h3>
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                @if(($financialInsight['source'] ?? '') === 'ai')
                                    🤖 AI Analysis
                                @else
                                    📊 Based on Data
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
                        <span>📜</span> Transaksi Terbaru
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
                                            <span class="text-xl">{{ $transaction->category->icon ?? '📌' }}</span>
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
                                    {{ $transaction->category->icon ?? '📌' }}
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
                        <p class="text-4xl mb-3">🎯</p>
                        <p class="text-slate-900 dark:text-white font-semibold mb-2">Belum ada transaksi</p>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Mulai catat keuanganmu sekarang!</p>
                        <div class="flex gap-3 justify-center flex-wrap">
                            <a href="{{ route('transactions.create-income') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition">
                                📥 Pemasukan
                            </a>
                            <a href="{{ route('transactions.create-expense') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition">
                                📤 Pengeluaran
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
                            <span class="text-xl">❤️</span>
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
                            <span>🎯</span> Target Tabungan
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
                        <span>💵</span> Uang Jajan
                    </h3>
                    <div class="space-y-3">
                        @if($walletSetting->monthly_allowance)
                            <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                                <span class="text-sm text-blue-700 dark:text-blue-400">📅 Per Bulan</span>
                                <span class="font-bold text-blue-700 dark:text-blue-400">Rp {{ number_format($walletSetting->monthly_allowance, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($walletSetting->weekly_allowance)
                            <div class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                                <span class="text-sm text-purple-700 dark:text-purple-400">📆 Per Minggu</span>
                                <span class="font-bold text-purple-700 dark:text-purple-400">Rp {{ number_format($walletSetting->weekly_allowance, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- AI Assistant CTA --}}
            <a href="{{ route('ai.chat') }}" class="block bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-2xl p-6 shadow-lg transition group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-2xl group-hover:scale-110 transition">
                        🤖
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
