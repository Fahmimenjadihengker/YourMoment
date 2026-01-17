@extends('layouts.app')

@section('title', 'Catat Pengeluaran')

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
                <span class="text-3xl mr-3">📤</span>
                Catat Pengeluaran
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Kelola pembelian dan pengeluaran kamu</p>
        </div>
    </div>

    {{-- Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Form --}}
        <div class="lg:col-span-2">
            <form action="{{ route('transactions.store') }}" method="POST" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                @csrf
                <input type="hidden" name="type" value="expense">

                {{-- Form Header --}}
                <div class="bg-gradient-to-r from-red-50 via-white to-orange-50 dark:from-red-900/20 dark:via-gray-800 dark:to-orange-900/20 px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Isi Data Pengeluaran</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Semua field wajib diisi kecuali yang optional</p>
                </div>

                {{-- Form Body --}}
                <div class="p-6 space-y-5">
                    {{-- Category --}}
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                            <span class="text-lg">📂</span> Kategori Pengeluaran <span class="text-red-500">*</span>
                        </label>
                        <select id="category_id" name="category_id" 
                                class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:border-red-500 transition @error('category_id') border-red-500 @enderror" 
                                required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->icon }} {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Amount --}}
                    <div class="bg-gradient-to-br from-red-50 to-white dark:from-red-900/20 dark:to-gray-800 rounded-xl p-5 border border-red-200 dark:border-red-800">
                        <label for="amount" class="block text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <span class="text-xl">💸</span> Berapa Biayanya? <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-700 dark:text-gray-300 font-bold text-xl">Rp</span>
                            <input type="number" id="amount" name="amount" 
                                   class="w-full pl-14 pr-4 py-4 border border-red-300 dark:border-red-700 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-2xl lg:text-3xl font-bold focus:ring-2 focus:ring-red-500 focus:border-red-500 transition @error('amount') border-red-500 @enderror"
                                   placeholder="0"
                                   step="1"
                                   min="1"
                                   value="{{ old('amount') }}"
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
                                <span>📅</span> Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="transaction_date" name="transaction_date"
                                   class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:border-red-500 transition @error('transaction_date') border-red-500 @enderror"
                                   value="{{ old('transaction_date', date('Y-m-d')) }}"
                                   required>
                            @error('transaction_date')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="payment_method" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span>💳</span> Metode Pembayaran
                            </label>
                            <input type="text" id="payment_method" name="payment_method"
                                   class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition"
                                   placeholder="Cash, Kartu Debit, E-wallet..."
                                   value="{{ old('payment_method') }}">
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                            <span>📝</span> Catatan Tambahan
                        </label>
                        <input type="text" id="description" name="description"
                               class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition"
                               placeholder="Contoh: Makan siang di kampus, bensin motor..."
                               value="{{ old('description') }}">
                    </div>
                </div>

                {{-- Form Footer --}}
                <div class="px-6 py-5 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex flex-col lg:flex-row gap-3">
                    <a href="{{ route('transactions.index') }}" 
                       class="lg:flex-1 px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-center">
                        Batal
                    </a>
                    <button type="submit" 
                            class="lg:flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold shadow-lg shadow-red-500/30 transition flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Catat Pengeluaran
                    </button>
                </div>
            </form>
        </div>

        {{-- Sidebar Tips --}}
        <div class="space-y-6">
            <div class="bg-gradient-to-br from-red-500 to-orange-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-start gap-3">
                    <span class="text-3xl">💡</span>
                    <div>
                        <p class="font-bold text-lg mb-2">Jangan Lupa Dicatat!</p>
                        <p class="text-red-100 text-sm">Catat SEMUA pengeluaran, baik besar maupun kecil. Ini kunci untuk memahami kemana uangmu pergi.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <span class="text-lg mr-2">📊</span>
                    Tips Pengeluaran
                </h4>
                <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start">
                        <span class="text-red-500 mr-2">•</span>
                        <span>Kategorisasi yang tepat membantu analisis</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-red-500 mr-2">•</span>
                        <span>Catat sesegera mungkin agar tidak lupa</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-red-500 mr-2">•</span>
                        <span>Review pengeluaran mingguan untuk pola</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
