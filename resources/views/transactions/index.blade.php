<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <span>Transaksi</span>
        </div>
    </x-slot>

    {{-- Page Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">Riwayat Transaksi</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Semua pemasukan dan pengeluaranmu</p>
        </div>
        <div class="hidden lg:flex gap-2">
            <a href="{{ route('transactions.create-income') }}"
                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                Pemasukan
            </a>
            <a href="{{ route('transactions.create-expense') }}"
                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                Pengeluaran
            </a>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-1.5 shadow-sm border border-slate-200 dark:border-slate-700 mb-4 inline-flex gap-1 flex-wrap">
        <a href="{{ route('transactions.index', array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'category_id' => $categoryId])) }}"
            class="px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap transition flex items-center gap-1.5 {{ !$filterType ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Semua
        </a>
        <a href="{{ route('transactions.index', array_filter(['type' => 'income', 'start_date' => $startDate, 'end_date' => $endDate, 'category_id' => $categoryId])) }}"
            class="px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap transition flex items-center gap-1.5 {{ $filterType === 'income' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
            Pemasukan
        </a>
        <a href="{{ route('transactions.index', array_filter(['type' => 'expense', 'start_date' => $startDate, 'end_date' => $endDate, 'category_id' => $categoryId])) }}"
            class="px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap transition flex items-center gap-1.5 {{ $filterType === 'expense' ? 'bg-red-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
            Pengeluaran
        </a>
    </div>

    <!-- Advanced Filter Form -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-6">
        <form action="{{ route('transactions.index') }}" method="GET" class="space-y-4">
            {{-- Preserve type filter if set --}}
            @if($filterType)
            <input type="hidden" name="type" value="{{ $filterType }}">
            @endif
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Start Date --}}
                <div>
                    <label for="start_date" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Dari Tanggal
                    </label>
                    <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                           class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                </div>

                {{-- End Date --}}
                <div>
                    <label for="end_date" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Sampai Tanggal
                    </label>
                    <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                           class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                </div>

                {{-- Category Filter --}}
                <div>
                    <label for="category_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                        Kategori
                    </label>
                    <select id="category_id" name="category_id"
                            class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                            {{ $category->icon }} {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-end gap-2">
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Terapkan
                    </button>
                    @if($hasActiveFilter)
                    <a href="{{ route('transactions.index') }}" 
                       class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg text-sm font-semibold transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Reset
                    </a>
                    @endif
                </div>
            </div>

            {{-- Active Filter Indicator --}}
            @if($hasActiveFilter)
            <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Filter aktif:</span>
                @if($startDate)<span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-600 rounded">Dari: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }}</span>@endif
                @if($endDate)<span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-600 rounded">Sampai: {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</span>@endif
                @if($categoryId)<span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-600 rounded">Kategori: {{ $categories->find($categoryId)?->name }}</span>@endif
                @if($filterType)<span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-600 rounded">Tipe: {{ $filterType === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</span>@endif
            </div>
            @endif
        </form>
    </div>

    <!-- Transactions Content -->
    @if($transactions->count() > 0)
    <!-- Desktop View: Table -->
    <div class="hidden lg:block bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($transactions as $transaction)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition border-l-4 {{ $transaction->type === 'income' ? 'border-l-emerald-500' : 'border-l-red-500' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                @if($transaction->category && $transaction->category->icon)
                                    <span class="text-2xl">{{ $transaction->category->icon }}</span>
                                @else
                                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                                @endif
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $transaction->category->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm max-w-xs truncate">{{ $transaction->description ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">{{ $transaction->formatted_datetime }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $transaction->payment_method ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-bold {{ $transaction->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $transaction->type === 'income' ? '+' : '−' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('transactions.edit', $transaction) }}"
                                    class="p-2 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline delete-transaction-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDeleteTransaction(this.closest('form'))" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile View: Cards -->
    <div class="lg:hidden space-y-3">
        @foreach($transactions as $transaction)
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 border-l-4 {{ $transaction->type === 'income' ? 'border-l-emerald-500' : 'border-l-red-500' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3 flex-1">
                    @if($transaction->category && $transaction->category->icon)
                        <div class="text-3xl">{{ $transaction->category->icon }}</div>
                    @else
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    @endif
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $transaction->category->name ?? 'Tanpa Kategori' }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $transaction->short_datetime }}</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="font-bold {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $transaction->type === 'income' ? '+' : '−' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </p>
                    <div class="flex items-center justify-end gap-1 mt-2">
                        <a href="{{ route('transactions.edit', $transaction) }}"
                            class="p-1.5 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded transition text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline delete-transaction-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmDeleteTransaction(this.closest('form'))" class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if($transaction->description || $transaction->payment_method)
            <div class="pt-3 border-t border-slate-100 dark:border-slate-700 space-y-1 text-xs mt-3">
                @if($transaction->description)
                <p class="text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ $transaction->description }}
                </p>
                @endif
                @if($transaction->payment_method)
                <p class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    {{ $transaction->payment_method }}
                </p>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $transactions->links() }}
    </div>
    @else
    <!-- Empty State -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-8 lg:p-12 text-center shadow-sm border border-slate-200 dark:border-slate-700">
        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <p class="text-slate-900 dark:text-white font-semibold text-lg mb-2">
            @if($hasActiveFilter)
                Tidak Ada Hasil
            @else
                Belum Ada Transaksi
            @endif
        </p>
        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6 max-w-md mx-auto">
            @if($hasActiveFilter)
                Tidak ada transaksi yang sesuai dengan filter yang dipilih. Coba ubah filter atau reset untuk melihat semua transaksi.
            @elseif($filterType)
                Belum ada data {{ $filterType === 'income' ? 'pemasukan' : 'pengeluaran' }} yang tercatat
            @else
                Mulai catat keuanganmu dan lihat ringkasan keuangan setiap hari!
            @endif
        </p>
        <div class="flex gap-3 justify-center flex-wrap">
            @if($hasActiveFilter)
            <a href="{{ route('transactions.index') }}" class="px-5 py-2.5 bg-slate-600 hover:bg-slate-700 text-white rounded-lg text-sm font-semibold transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset Filter
            </a>
            @endif
            <a href="{{ route('transactions.create-income') }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                Tambah Pemasukan
            </a>
            <a href="{{ route('transactions.create-expense') }}" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                Tambah Pengeluaran
            </a>
        </div>
    </div>
    @endif

    <!-- Mobile Quick Add Buttons -->
    <div class="lg:hidden fixed bottom-20 right-4 left-4 flex gap-2 z-40">
        <a href="{{ route('transactions.create-income') }}"
            class="flex-1 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition shadow-lg flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
            Pemasukan
        </a>
        <a href="{{ route('transactions.create-expense') }}"
            class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold transition shadow-lg flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
            Pengeluaran
        </a>
    </div>

    @push('scripts')
    <script>
    function confirmDeleteTransaction(form) {
        window.confirmDelete('Hapus transaksi ini?', 'Transaksi yang dihapus tidak bisa dikembalikan.').then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
    </script>
    @endpush
</x-app-layout>