@extends('layouts.app')

@section('title', 'Analisis Keuangan')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                <span class="text-3xl mr-3">🤖</span>
                Analisis Keuangan AI
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Analisis pengeluaran {{ $period['start'] ?? '-' }} - {{ $period['end'] ?? '-' }}</p>
        </div>
        <a href="{{ route('dashboard') }}" 
           class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-semibold text-sm px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Dashboard
        </a>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Main Recommendation Card --}}
            <div class="bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-600 rounded-xl p-6 lg:p-8 shadow-xl relative overflow-hidden">
                <div class="absolute -top-32 -right-32 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center text-3xl">
                            🤖
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">AI Financial Insight</h2>
                            <p class="text-emerald-100 text-sm">Berdasarkan pola pengeluaran 7 hari terakhir</p>
                        </div>
                    </div>

                    {{-- Summary Stats --}}
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-white/15 backdrop-blur rounded-xl p-4 border border-white/20">
                            <p class="text-emerald-100 text-xs font-medium uppercase tracking-wider mb-1">Total Pengeluaran</p>
                            <p class="text-xl lg:text-2xl font-bold text-white">Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white/15 backdrop-blur rounded-xl p-4 border border-white/20">
                            <p class="text-emerald-100 text-xs font-medium uppercase tracking-wider mb-1">Kategori Tercatat</p>
                            <p class="text-xl lg:text-2xl font-bold text-white">{{ $categoryBreakdown?->count() ?? 0 }}</p>
                        </div>
                    </div>

                    {{-- Recommendation Text --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-lg">
                        <div class="prose prose-sm dark:prose-invert max-w-none">
                            @if(!empty($recommendation))
                                @foreach(explode("\n\n", $recommendation) as $paragraph)
                                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-3 last:mb-0">{{ $paragraph }}</p>
                                @endforeach
                            @else
                                <p class="text-gray-500 dark:text-gray-400 text-center py-4">Belum ada data untuk dianalisis. Tambahkan beberapa transaksi terlebih dahulu. 📝</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Category Breakdown Card --}}
            @if($categoryBreakdown && $categoryBreakdown->count() > 0 && !empty($categoryPercentages))
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                        📊 <span>Breakdown Pengeluaran</span>
                    </h3>

                    <div class="space-y-4">
                        @foreach($categoryPercentages as $category)
                            <div class="group">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">{{ $category['icon'] }}</span>
                                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $category['name'] }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $category['percentage'] }}%</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">(Rp {{ number_format($category['total'], 0, ',', '.') }})</span>
                                    </div>
                                </div>
                                <div class="h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500"
                                         style="width: {{ min($category['percentage'], 100) }}%; background-color: {{ $category['color'] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Column - Reference --}}
        <div class="space-y-6">
            {{-- Ideal Percentage Reference --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    💡 <span>Persentase Ideal</span>
                </h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Standar alokasi pengeluaran sehat untuk mahasiswa:</p>

                <div class="space-y-3">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🍔</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Makan</span>
                        </div>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold text-sm">40-50%</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🚗</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Transport</span>
                        </div>
                        <span class="text-blue-600 dark:text-blue-400 font-bold text-sm">10-20%</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">☕</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Nongkrong</span>
                        </div>
                        <span class="text-pink-600 dark:text-pink-400 font-bold text-sm">10-20%</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">📚</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Akademik</span>
                        </div>
                        <span class="text-purple-600 dark:text-purple-400 font-bold text-sm">5-10%</span>
                    </div>
                    <div class="bg-emerald-50 dark:bg-emerald-900/30 rounded-lg p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">💰</span>
                            <span class="text-sm font-medium text-emerald-800 dark:text-emerald-300">Tabungan</span>
                        </div>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold text-sm">Sisanya!</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-6 text-white">
                <h4 class="font-semibold mb-2">🤖 Butuh Saran Lebih?</h4>
                <p class="text-sm text-white/80 mb-4">Tanya AI Assistant untuk tips keuangan yang lebih personal!</p>
                <a href="{{ route('ai.chat') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                    Tanya AI
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            {{-- Action Buttons --}}
            <div class="space-y-3">
                <a href="{{ route('dashboard') }}"
                   class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('transactions.index') }}"
                   class="w-full flex items-center justify-center gap-2 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold py-3 px-6 rounded-lg transition-colors border border-gray-200 dark:border-gray-600">
                    📋 Lihat Transaksi
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
