@extends('layouts.app')

@section('title', 'Edit Transaksi')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center space-x-4 mb-6">
        <a href="{{ route('transactions.index') }}" 
           class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                @if($transaction->type === 'income')
                <svg class="w-8 h-8 mr-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                @else
                <svg class="w-8 h-8 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                @endif
                Edit {{ $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Perbaiki data transaksimu</p>
        </div>
    </div>

    {{-- Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Form --}}
        <div class="lg:col-span-2">
            <form action="{{ route('transactions.update', $transaction) }}" method="POST" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                @csrf
                @method('PUT')

                {{-- Form Header --}}
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 {{ $transaction->type === 'income' 
                    ? 'bg-gradient-to-r from-emerald-50 via-white to-teal-50 dark:from-emerald-900/20 dark:via-gray-800 dark:to-teal-900/20' 
                    : 'bg-gradient-to-r from-red-50 via-white to-orange-50 dark:from-red-900/20 dark:via-gray-800 dark:to-orange-900/20' }}">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Edit Data {{ $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Semua field wajib diisi kecuali yang optional</p>
                </div>

                {{-- Form Body --}}
                <div class="p-6 space-y-5">
                    {{-- Category --}}
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            Kategori <span class="text-red-500">*</span>
                        </label>
                        <select id="category_id" name="category_id"
                                class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 {{ $transaction->type === 'income' ? 'focus:ring-emerald-500 focus:border-emerald-500' : 'focus:ring-red-500 focus:border-red-500' }} transition @error('category_id') border-red-500 @enderror"
                                required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->icon }} {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Amount --}}
                    <div class="rounded-xl p-5 border {{ $transaction->type === 'income' 
                        ? 'bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-900/20 dark:to-gray-800 border-emerald-200 dark:border-emerald-800' 
                        : 'bg-gradient-to-br from-red-50 to-white dark:from-red-900/20 dark:to-gray-800 border-red-200 dark:border-red-800' }}">
                        <label for="amount" class="block text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 {{ $transaction->type === 'income' ? 'text-emerald-500' : 'text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Jumlah <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-700 dark:text-gray-300 font-bold text-xl">Rp</span>
                            <input type="number" id="amount" name="amount"
                                   class="w-full pl-14 pr-4 py-4 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-2xl lg:text-3xl font-bold focus:ring-2 {{ $transaction->type === 'income' ? 'border-emerald-300 dark:border-emerald-700 focus:ring-emerald-500 focus:border-emerald-500' : 'border-red-300 dark:border-red-700 focus:ring-red-500 focus:border-red-500' }} transition @error('amount') border-red-500 @enderror"
                                   placeholder="0"
                                   step="1"
                                   min="1"
                                   value="{{ old('amount', intval($transaction->amount)) }}"
                                   required>
                        </div>
                        @error('amount')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Date & Payment Grid --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label for="transaction_date" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="transaction_date" name="transaction_date"
                                   class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition @error('transaction_date') border-red-500 @enderror"
                                   value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}"
                                   required>
                            @error('transaction_date')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="payment_method" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                Metode Pembayaran
                            </label>
                            <input type="text" id="payment_method" name="payment_method"
                                   class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition"
                                   placeholder="Transfer, Cash, E-wallet..."
                                   value="{{ old('payment_method', $transaction->payment_method) }}">
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Catatan Tambahan
                        </label>
                        <input type="text" id="description" name="description"
                               class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition"
                               placeholder="Detail tambahan..."
                               value="{{ old('description', $transaction->description) }}">
                    </div>
                </div>

                {{-- Form Footer --}}
                <div class="px-6 py-5 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex flex-col lg:flex-row gap-3">
                    <a href="{{ route('transactions.index') }}"
                       class="lg:flex-1 px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-center">
                        Batal
                    </a>
                    <button type="submit"
                            class="lg:flex-1 px-6 py-3 text-white rounded-lg font-semibold shadow-lg transition flex items-center justify-center {{ $transaction->type === 'income' 
                                ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/30' 
                                : 'bg-red-600 hover:bg-red-700 shadow-red-500/30' }}">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>

            {{-- Delete Section --}}
            <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Zona Berbahaya
                </h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Menghapus transaksi ini akan mempengaruhi balance walletmu.</p>

                <form action="{{ route('transactions.destroy', $transaction) }}" method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus transaksi ini? Balance akan di-update otomatis.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-6 py-3 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg font-semibold hover:bg-red-200 dark:hover:bg-red-900/50 transition border border-red-200 dark:border-red-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus Transaksi
                    </button>
                </form>
            </div>
        </div>

        {{-- Sidebar Info --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Detail Transaksi</h4>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Tipe</span>
                        <span class="font-medium {{ $transaction->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Dibuat</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $transaction->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Terakhir diupdate</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $transaction->updated_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-6">
                <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    Perhatian
                </h4>
                <p class="text-sm text-amber-700 dark:text-amber-400">
                    Mengubah jumlah transaksi akan otomatis memperbarui saldo wallet Anda.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
