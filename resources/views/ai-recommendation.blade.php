<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-start gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">🤖 Rekomendasi Keuanganmu</h1>
                <p class="text-slate-500 text-sm mt-1.5">Analisis pengeluaran {{ $period['start'] }} - {{ $period['end'] }}</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-700 font-semibold text-sm px-4 py-2 rounded-lg hover:bg-slate-100 transition">
                <span>←</span> Dashboard
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Main Recommendation Card --}}
        <div class="bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-600 rounded-3xl p-8 sm:p-10 border border-emerald-500 shadow-2xl relative overflow-hidden">
            {{-- Decorative blobs --}}
            <div class="absolute -top-32 -right-32 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>

            <div class="relative z-10">
                {{-- AI Icon & Title --}}
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center text-4xl">
                        🤖
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-white">AI Financial Insight</h2>
                        <p class="text-emerald-100 text-sm">Berdasarkan pola pengeluaran 7 hari terakhir</p>
                    </div>
                </div>

                {{-- Summary Stats --}}
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="bg-white/15 backdrop-blur rounded-2xl p-4 border border-white/20">
                        <p class="text-emerald-100 text-xs font-medium uppercase tracking-wider mb-1">Total Pengeluaran</p>
                        <p class="text-2xl font-bold text-white">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white/15 backdrop-blur rounded-2xl p-4 border border-white/20">
                        <p class="text-emerald-100 text-xs font-medium uppercase tracking-wider mb-1">Kategori Tercatat</p>
                        <p class="text-2xl font-bold text-white">{{ $categoryBreakdown->count() }}</p>
                    </div>
                </div>

                {{-- Recommendation Text --}}
                <div class="bg-white/95 backdrop-blur rounded-2xl p-6 shadow-lg">
                    <div class="prose prose-slate max-w-none">
                        @foreach(explode("\n\n", $recommendation) as $paragraph)
                        <p class="text-slate-700 leading-relaxed mb-4 last:mb-0">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Category Breakdown Card --}}
        @if($categoryBreakdown->count() > 0)
        <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                📊 <span>Breakdown Pengeluaran</span>
            </h3>

            <div class="space-y-4">
                @foreach($categoryPercentages as $category)
                <div class="group">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ $category['icon'] }}</span>
                            <span class="font-semibold text-slate-800">{{ $category['name'] }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-slate-900">{{ $category['percentage'] }}%</span>
                            <span class="text-xs text-slate-500 ml-2">(Rp {{ number_format($category['total'], 0, ',', '.') }})</span>
                        </div>
                    </div>
                    <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                        <div
                            class="h-full rounded-full transition-all duration-500"
                            style="width: {{ min($category['percentage'], 100) }}%; background-color: {{ $category['color'] }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Ideal Percentage Reference --}}
        <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-200">
            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                💡 <span>Referensi Persentase Ideal</span>
            </h3>
            <p class="text-slate-600 text-sm mb-4">Standar umum alokasi pengeluaran yang sehat untuk mahasiswa/anak muda:</p>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <div class="bg-white rounded-xl p-4 border border-slate-200 text-center">
                    <span class="text-2xl block mb-2">🍔</span>
                    <p class="font-semibold text-slate-800 text-sm">Makan</p>
                    <p class="text-emerald-600 font-bold">40-50%</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200 text-center">
                    <span class="text-2xl block mb-2">🚗</span>
                    <p class="font-semibold text-slate-800 text-sm">Transport</p>
                    <p class="text-blue-600 font-bold">10-20%</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200 text-center">
                    <span class="text-2xl block mb-2">☕</span>
                    <p class="font-semibold text-slate-800 text-sm">Nongkrong</p>
                    <p class="text-pink-600 font-bold">10-20%</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200 text-center">
                    <span class="text-2xl block mb-2">📚</span>
                    <p class="font-semibold text-slate-800 text-sm">Akademik</p>
                    <p class="text-purple-600 font-bold">5-10%</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200 text-center col-span-2 sm:col-span-1">
                    <span class="text-2xl block mb-2">💰</span>
                    <p class="font-semibold text-slate-800 text-sm">Tabungan</p>
                    <p class="text-emerald-600 font-bold">Sisanya</p>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('dashboard') }}"
                class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-4 px-6 rounded-xl text-center transition-colors shadow-lg">
                ← Kembali ke Dashboard
            </a>
            <a href="{{ route('transactions.index') }}"
                class="flex-1 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-4 px-6 rounded-xl text-center transition-colors border border-slate-200">
                📋 Lihat Semua Transaksi
            </a>
        </div>
    </div>
</x-app-layout>