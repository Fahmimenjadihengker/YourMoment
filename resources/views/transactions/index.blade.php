<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-start gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Riwayat Transaksi 📜</h1>
                <p class="text-slate-500 text-sm mt-1.5">Semua pemasukan dan pengeluaranmu</p>
            </div>
            <div class="hidden sm:flex gap-2">
                <a href="{{ route('transactions.create-income') }}"
                    class="px-5 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl text-sm font-bold hover:from-emerald-600 hover:to-teal-700 transition shadow-lg">
                    📥 Income
                </a>
                <a href="{{ route('transactions.create-expense') }}"
                    class="px-5 py-3 bg-gradient-to-r from-red-500 to-orange-600 text-white rounded-xl text-sm font-bold hover:from-red-600 hover:to-orange-700 transition shadow-lg">
                    📤 Expense
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Filter Tabs -->
    <div class="bg-white rounded-2xl p-2 shadow-lg border border-slate-200 mb-6 inline-flex gap-2 flex-wrap">
        <a href="{{ route('transactions.index') }}"
            class="px-5 py-3 rounded-xl font-bold whitespace-nowrap transition {{ !$filterType ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-md' : 'text-slate-700 hover:bg-slate-100' }}">
            📊 Semua
        </a>
        <a href="{{ route('transactions.index', ['type' => 'income']) }}"
            class="px-5 py-3 rounded-xl font-bold whitespace-nowrap transition {{ $filterType === 'income' ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-md' : 'text-slate-700 hover:bg-slate-100' }}">
            📥 Income
        </a>
        <a href="{{ route('transactions.index', ['type' => 'expense']) }}"
            class="px-5 py-3 rounded-xl font-bold whitespace-nowrap transition {{ $filterType === 'expense' ? 'bg-gradient-to-r from-red-500 to-orange-600 text-white shadow-md' : 'text-slate-700 hover:bg-slate-100' }}">
            📤 Expense
        </a>
    </div>

    <!-- Transactions Content -->
    @if($transactions->count() > 0)
    <!-- Desktop View: Table -->
    <div class="hidden md:block bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-slate-100 to-slate-50 border-b-2 border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-900 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-900 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-900 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-900 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-900 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-900 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($transactions as $transaction)
                    <tr class="hover:bg-slate-50 transition border-l-4 {{ $transaction->type === 'income' ? 'border-l-emerald-500' : 'border-l-red-500' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ $transaction->category->icon ?? '📌' }}</span>
                                <span class="font-bold text-slate-900">{{ $transaction->category->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-600 text-sm">{{ $transaction->description ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-700">{{ $transaction->transaction_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $transaction->payment_method ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-lg {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $transaction->type === 'income' ? '+' : '−' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('transactions.edit', $transaction) }}"
                                    class="px-3 py-1.5 bg-amber-100 text-amber-700 rounded-lg text-xs font-bold hover:bg-amber-200 transition"
                                    title="Edit">
                                    ✏️
                                </a>
                                <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Yakin hapus transaksi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-xs font-bold hover:bg-red-200 transition"
                                        title="Hapus">
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
    <div class="md:hidden space-y-3">
        @foreach($transactions as $transaction)
        <div class="bg-white rounded-2xl p-5 shadow-md border border-slate-200 border-l-4 {{ $transaction->type === 'income' ? 'border-l-emerald-500' : 'border-l-red-500' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3 flex-1">
                    <div class="text-4xl">{{ $transaction->category->icon ?? '📌' }}</div>
                    <div>
                        <p class="font-bold text-slate-900 text-sm">{{ $transaction->category->name }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $transaction->transaction_date->format('d M Y') }}</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="font-bold text-lg {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $transaction->type === 'income' ? '+' : '−' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </p>
                    <div class="flex items-center justify-end gap-2 mt-2">
                        <a href="{{ route('transactions.edit', $transaction) }}"
                            class="px-2 py-1 bg-amber-100 text-amber-700 rounded text-xs font-bold hover:bg-amber-200 transition">
                            ✏️
                        </a>
                        <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline"
                            onsubmit="return confirm('Yakin hapus transaksi ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold hover:bg-red-200 transition">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if($transaction->description || $transaction->payment_method)
            <div class="pt-3 border-t border-slate-200 space-y-1 text-xs mt-3">
                @if($transaction->description)
                <p class="text-slate-600">📝 {{ $transaction->description }}</p>
                @endif
                @if($transaction->payment_method)
                <p class="text-slate-500">💳 {{ $transaction->payment_method }}</p>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-md">
            {{ $transactions->links() }}
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-3xl p-12 text-center border-2 border-slate-200 shadow-lg">
        <p class="text-7xl mb-4">🎯</p>
        <p class="text-slate-900 font-bold text-xl mb-2">Belum ada transaksi</p>
        <p class="text-slate-600 text-sm mb-8 max-w-md mx-auto">
            @if($filterType)
            Belum ada transaksi {{ $filterType }} yang tercatat. Mulai catat sekarang!
            @else
            Mulai catat keuanganmu dan lihat perkembangan finansialmu setiap hari!
            @endif
        </p>
        <div class="flex gap-3 justify-center flex-col sm:flex-row">
            <a href="{{ route('transactions.create-income') }}"
                class="px-7 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-bold hover:from-emerald-600 hover:to-teal-700 transition shadow-lg inline-block">
                📥 Tambah Income
            </a>
            <a href="{{ route('transactions.create-expense') }}"
                class="px-7 py-4 bg-gradient-to-r from-red-500 to-orange-600 text-white rounded-xl font-bold hover:from-red-600 hover:to-orange-700 transition shadow-lg inline-block">
                📤 Tambah Expense
            </a>
        </div>
    </div>
    @endif

    <!-- Mobile Quick Add Buttons -->
    <div class="md:hidden fixed bottom-20 right-4 left-4 flex gap-2">
        <a href="{{ route('transactions.create-income') }}"
            class="flex-1 px-4 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl text-sm font-bold hover:from-emerald-600 hover:to-teal-700 transition shadow-lg text-center">
            📥 Income
        </a>
        <a href="{{ route('transactions.create-expense') }}"
            class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-orange-600 text-white rounded-xl text-sm font-bold hover:from-red-600 hover:to-orange-700 transition shadow-lg text-center">
            📤 Expense
        </a>
    </div>
</x-app-layout>