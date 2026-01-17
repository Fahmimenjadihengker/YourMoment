<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span class="text-xl">💳</span>
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
                <span>📥</span> Pemasukan
            </a>
            <a href="{{ route('transactions.create-expense') }}"
                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold transition shadow-sm flex items-center gap-2">
                <span>📤</span> Pengeluaran
            </a>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-1.5 shadow-sm border border-slate-200 dark:border-slate-700 mb-6 inline-flex gap-1 flex-wrap">
        <a href="{{ route('transactions.index') }}"
            class="px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap transition {{ !$filterType ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            📊 Semua
        </a>
        <a href="{{ route('transactions.index', ['type' => 'income']) }}"
            class="px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap transition {{ $filterType === 'income' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            📥 Pemasukan
        </a>
        <a href="{{ route('transactions.index', ['type' => 'expense']) }}"
            class="px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap transition {{ $filterType === 'expense' ? 'bg-red-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            📤 Pengeluaran
        </a>
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
                                <span class="text-2xl">{{ $transaction->category->icon ?? '📌' }}</span>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $transaction->category->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm max-w-xs truncate">{{ $transaction->description ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">{{ $transaction->transaction_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $transaction->payment_method ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-bold {{ $transaction->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $transaction->type === 'income' ? '+' : '−' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('transactions.edit', $transaction) }}"
                                    class="p-2 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition" title="Edit">
                                    ✏️
                                </a>
                                <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Yakin hapus transaksi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="Hapus">
                                        🗑️
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
                    <div class="text-3xl">{{ $transaction->category->icon ?? '📌' }}</div>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $transaction->category->name ?? 'Tanpa Kategori' }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $transaction->transaction_date?->format('d M Y') ?? '-' }}</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="font-bold {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $transaction->type === 'income' ? '+' : '−' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </p>
                    <div class="flex items-center justify-end gap-1 mt-2">
                        <a href="{{ route('transactions.edit', $transaction) }}"
                            class="p-1.5 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded transition text-sm">
                            ✏️
                        </a>
                        <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline"
                            onsubmit="return confirm('Yakin hapus transaksi ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition text-sm">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if($transaction->description || $transaction->payment_method)
            <div class="pt-3 border-t border-slate-100 dark:border-slate-700 space-y-1 text-xs mt-3">
                @if($transaction->description)
                <p class="text-slate-600 dark:text-slate-400">📝 {{ $transaction->description }}</p>
                @endif
                @if($transaction->payment_method)
                <p class="text-slate-500 dark:text-slate-400">💳 {{ $transaction->payment_method }}</p>
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
        <p class="text-4xl mb-3">📜</p>
        <p class="text-slate-900 dark:text-white font-semibold text-lg mb-2">Belum Ada Transaksi</p>
        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6 max-w-md mx-auto">
            @if($filterType)
                Belum ada data {{ $filterType }} yang tercatat
            @else
                Mulai catat keuanganmu dan lihat ringkasan keuangan setiap hari!
            @endif
        </p>
        <div class="flex gap-3 justify-center flex-wrap">
            <a href="{{ route('transactions.create-income') }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition">
                📥 Tambah Pemasukan
            </a>
            <a href="{{ route('transactions.create-expense') }}" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition">
                📤 Tambah Pengeluaran
            </a>
        </div>
    </div>
    @endif

    <!-- Mobile Quick Add Buttons -->
    <div class="lg:hidden fixed bottom-20 right-4 left-4 flex gap-2 z-40">
        <a href="{{ route('transactions.create-income') }}"
            class="flex-1 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition shadow-lg text-center">
            📥 Pemasukan
        </a>
        <a href="{{ route('transactions.create-expense') }}"
            class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold transition shadow-lg text-center">
            📤 Pengeluaran
        </a>
    </div>
</x-app-layout>