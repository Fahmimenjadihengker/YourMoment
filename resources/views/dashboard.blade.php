<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-start gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Halo! 👋</h1>
                <p class="text-slate-500 text-sm mt-1.5">{{ $currentMonth }} - Lihat ringkasan keuanganmu</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="hidden sm:inline-flex items-center gap-2 text-emerald-600 hover:text-emerald-700 font-semibold text-sm px-4 py-2 rounded-lg hover:bg-emerald-50 transition">
                <span>📋</span> Lihat Semua
            </a>
        </div>
    </x-slot>

    <!-- HERO SECTION - UTAMA & KUAT -->
    <div class="bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-600 rounded-3xl p-8 sm:p-12 mb-8 border border-emerald-500 shadow-2xl relative overflow-hidden">
        <!-- Decorative blobs untuk depth -->
        <div class="absolute -top-32 -right-32 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>

        <div class="relative z-10">
            <!-- Balance Display -->
            <p class="text-emerald-100 text-sm font-semibold uppercase tracking-wider mb-3">💰 Total Balance Kamu</p>
            <p class="text-6xl sm:text-7xl font-black text-white tracking-tight mb-8">
                Rp {{ number_format($walletSetting->balance ?? 0, 0, ',', '.') }}
            </p>

            <!-- Quick Actions TERINTEGRASI dalam Hero -->
            <div class="grid grid-cols-2 gap-3 mb-8">
                <a href="{{ route('transactions.create-income') }}"
                    class="group bg-white/95 backdrop-blur hover:bg-white rounded-2xl px-6 py-4 text-center transition-all duration-200 shadow-lg hover:shadow-xl">
                    <div class="text-3xl mb-2">📥</div>
                    <p class="font-bold text-slate-900 text-sm">Terima Uang</p>
                    <p class="text-xs text-emerald-600 mt-0.5">Tambah Income</p>
                </a>
                <a href="{{ route('transactions.create-expense') }}"
                    class="group bg-white/95 backdrop-blur hover:bg-white rounded-2xl px-6 py-4 text-center transition-all duration-200 shadow-lg hover:shadow-xl">
                    <div class="text-3xl mb-2">📤</div>
                    <p class="font-bold text-slate-900 text-sm">Keluar Uang</p>
                    <p class="text-xs text-red-600 mt-0.5">Tambah Expense</p>
                </a>
            </div>

            <!-- AI Recommendation Quick Action -->
            <a href="{{ route('ai-recommendation') }}"
                class="block bg-white/15 backdrop-blur hover:bg-white/25 rounded-2xl px-6 py-4 text-center transition-all duration-200 border border-white/20 mb-8">
                <div class="flex items-center justify-center gap-3">
                    <span class="text-2xl">🤖</span>
                    <div class="text-left">
                        <p class="font-bold text-white text-sm">AI Financial Insight</p>
                        <p class="text-xs text-emerald-100">Lihat analisis pengeluaranmu</p>
                    </div>
                    <span class="text-white/60 ml-2">→</span>
                </div>
            </a>

            <!-- Target Savings Progress (jika ada) -->
            @if($walletSetting && $walletSetting->financial_goal)
            <div class="bg-white/15 backdrop-blur rounded-2xl p-5 border border-white/20">
                <div class="flex items-center justify-between gap-4 mb-3">
                    <p class="text-white font-semibold text-sm">🎯 Target Tabungan</p>
                    <p class="text-white font-bold">Rp {{ number_format($walletSetting->financial_goal, 0, ',', '.') }}</p>
                </div>
                @php
                $progress = $walletSetting->financial_goal > 0
                ? min(100, (($walletSetting->balance ?? 0) / $walletSetting->financial_goal) * 100)
                : 0;
                @endphp
                <div class="w-full bg-white/20 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-white h-2.5 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                </div>
                <div class="flex items-center justify-between gap-4 mt-3">
                    <p class="text-white/80 text-xs">{{ round($progress) }}% tercapai</p>
                    <p class="text-white text-xs font-semibold">Sisa: Rp {{ number_format(max(0, $walletSetting->financial_goal - ($walletSetting->balance ?? 0)), 0, ',', '.') }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- SUMMARY SECTION - GROUPED COMPACT -->
    <div class="grid grid-cols-2 gap-4 mb-8">
        <!-- Income Summary -->
        <div class="bg-gradient-to-br from-emerald-50 to-white rounded-2xl p-6 border border-emerald-200 shadow-lg hover:shadow-xl transition">
            <div class="flex items-center justify-between gap-3 mb-3">
                <p class="text-xs font-bold text-emerald-700 uppercase tracking-widest">📥 Income</p>
                <span class="text-2xl">💚</span>
            </div>
            <p class="text-3xl sm:text-4xl font-black text-emerald-600">
                +Rp {{ number_format($totalIncome, 0, ',', '.') }}
            </p>
            <p class="text-xs text-emerald-600 font-semibold mt-2">Bulan Ini</p>
        </div>

        <!-- Expense Summary -->
        <div class="bg-gradient-to-br from-red-50 to-white rounded-2xl p-6 border border-red-200 shadow-lg hover:shadow-xl transition">
            <div class="flex items-center justify-between gap-3 mb-3">
                <p class="text-xs font-bold text-red-700 uppercase tracking-widest">📤 Expense</p>
                <span class="text-2xl">💔</span>
            </div>
            <p class="text-3xl sm:text-4xl font-black text-red-600">
                −Rp {{ number_format($totalExpense, 0, ',', '.') }}
            </p>
            <p class="text-xs text-red-600 font-semibold mt-2">Bulan Ini</p>
        </div>
    </div>

    <!-- Allowance Info -->
    <!-- ALLOWANCE + RECENT TRANSACTIONS - GROUPED SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Allowance (Compact) -->
        @if($walletSetting && ($walletSetting->monthly_allowance || $walletSetting->weekly_allowance))
        <div class="lg:col-span-1 bg-gradient-to-br from-blue-50 to-white rounded-2xl p-6 border border-blue-200 shadow-lg">
            <h3 class="font-bold text-slate-900 mb-4 text-sm flex items-center gap-2">
                <span class="text-xl">💰</span> Uang Jajanmu
            </h3>
            <div class="space-y-2">
                @if($walletSetting->monthly_allowance)
                <div class="bg-white rounded-xl p-3 border border-blue-100 shadow-sm hover:shadow-md transition">
                    <p class="text-xs text-blue-600 font-bold uppercase tracking-wide">📅 Per Bulan</p>
                    <p class="text-lg font-bold text-blue-700 mt-1">Rp {{ number_format($walletSetting->monthly_allowance, 0, ',', '.') }}</p>
                </div>
                @endif
                @if($walletSetting->weekly_allowance)
                <div class="bg-white rounded-xl p-3 border border-purple-100 shadow-sm hover:shadow-md transition">
                    <p class="text-xs text-purple-600 font-bold uppercase tracking-wide">📆 Per Minggu</p>
                    <p class="text-lg font-bold text-purple-700 mt-1">Rp {{ number_format($walletSetting->weekly_allowance, 0, ',', '.') }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Right Column: Recent Transactions -->
        <div class="{{ ($walletSetting && ($walletSetting->monthly_allowance || $walletSetting->weekly_allowance)) ? 'lg:col-span-2' : 'lg:col-span-3' }} bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-slate-100 to-white border-b border-slate-200">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span>📜</span> Transaksi Terakhir
                </h3>
            </div>

            @if($recentTransactions->count() > 0)
            <div class="max-h-96 overflow-y-auto divide-y divide-slate-100">
                @foreach($recentTransactions as $transaction)
                <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors border-l-4 {{ $transaction->type === 'income' ? 'border-l-emerald-500' : 'border-l-red-500' }}">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-11 h-11 bg-slate-100 rounded-xl flex items-center justify-center text-lg flex-shrink-0 ring-1 ring-black/5">
                            {{ $transaction->category->icon ?? '📌' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-900 text-sm">{{ $transaction->category->name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $transaction->transaction_date->format('d M Y') }}</p>
                            @if($transaction->description)
                            <p class="text-xs text-slate-600 mt-1 truncate">{{ $transaction->description }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right ml-4 flex-shrink-0">
                        <p class="font-bold text-sm {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $transaction->type === 'income' ? '+' : '−' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-12 text-center bg-slate-50">
                <p class="text-5xl mb-3">🎯</p>
                <p class="text-slate-900 font-semibold mb-2">Belum ada transaksi</p>
                <p class="text-slate-600 text-sm mb-6">Mulai catat keuanganmu sekarang</p>
                <div class="flex gap-2 justify-center flex-wrap">
                    <a href="{{ route('transactions.create-income') }}"
                        class="text-emerald-600 hover:text-emerald-700 font-bold text-sm px-4 py-2 rounded-lg hover:bg-emerald-50 transition">
                        📥 Income
                    </a>
                    <a href="{{ route('transactions.create-expense') }}"
                        class="text-red-600 hover:text-red-700 font-bold text-sm px-4 py-2 rounded-lg hover:bg-red-50 transition">
                        📤 Expense
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>