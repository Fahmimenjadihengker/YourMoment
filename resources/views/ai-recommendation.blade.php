@extends('layouts.app')

@section('title', 'Analisis Keuangan')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="w-8 h-8 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
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
                        <div class="w-14 h-14 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
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
                                <p class="text-gray-500 dark:text-gray-400 text-center py-4">Belum ada data untuk dianalisis. Tambahkan beberapa transaksi terlebih dahulu.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Category Breakdown Card --}}
            @if($categoryBreakdown && $categoryBreakdown->count() > 0 && !empty($categoryPercentages))
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Breakdown Pengeluaran</span>
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
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span>Persentase Ideal</span>
                </h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Standar alokasi pengeluaran sehat untuk mahasiswa:</p>

                <div class="space-y-3">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Makan</span>
                        </div>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold text-sm">40-50%</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Transport</span>
                        </div>
                        <span class="text-blue-600 dark:text-blue-400 font-bold text-sm">10-20%</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Nongkrong</span>
                        </div>
                        <span class="text-pink-600 dark:text-pink-400 font-bold text-sm">10-20%</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Akademik</span>
                        </div>
                        <span class="text-purple-600 dark:text-purple-400 font-bold text-sm">5-10%</span>
                    </div>
                    <div class="bg-emerald-50 dark:bg-emerald-900/30 rounded-lg p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span class="text-sm font-medium text-emerald-800 dark:text-emerald-300">Tabungan</span>
                        </div>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold text-sm">Sisanya!</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-6 text-white">
                <h4 class="font-semibold mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    Butuh Saran Lebih?
                </h4>
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
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    Lihat Transaksi
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
