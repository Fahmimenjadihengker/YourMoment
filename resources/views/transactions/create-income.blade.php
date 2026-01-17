@extends('layouts.app')

@section('title', 'Terima Uang')

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
                <svg class="w-8 h-8 mr-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                Terima Uang
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Catat sumber penghasilan atau bonus kamu</p>
        </div>
    </div>

    {{-- Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Form --}}
        <div class="lg:col-span-2">
            <form action="{{ route('transactions.store') }}" method="POST" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                @csrf
                <input type="hidden" name="type" value="income">

                {{-- Form Header --}}
                <div class="bg-gradient-to-r from-emerald-50 via-white to-teal-50 dark:from-emerald-900/20 dark:via-gray-800 dark:to-teal-900/20 px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Isi Data Income</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Semua field wajib diisi kecuali yang optional</p>
                </div>

                {{-- Form Body --}}
                <div class="p-6 space-y-5">
                    {{-- Category --}}
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            Kategori Sumber Uang <span class="text-red-500">*</span>
                        </label>
                        <select id="category_id" name="category_id" 
                                class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('category_id') border-red-500 @enderror" 
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
                    <div class="bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-900/20 dark:to-gray-800 rounded-xl p-5 border border-emerald-200 dark:border-emerald-800">
                        <label for="amount" class="block text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Berapa Jumlahnya? <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-700 dark:text-gray-300 font-bold text-xl">Rp</span>
                            <input type="number" id="amount" name="amount" 
                                   class="w-full pl-14 pr-4 py-4 border border-emerald-300 dark:border-emerald-700 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-2xl lg:text-3xl font-bold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('amount') border-red-500 @enderror"
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
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="transaction_date" name="transaction_date"
                                   class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('transaction_date') border-red-500 @enderror"
                                   value="{{ old('transaction_date', date('Y-m-d')) }}"
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
                                   class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                   placeholder="Transfer, Cash, E-wallet..."
                                   value="{{ old('payment_method') }}">
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Catatan Tambahan
                        </label>
                        <input type="text" id="description" name="description"
                               class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                               placeholder="Contoh: Gaji freelance, bonus dari orang tua..."
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
                            class="lg:flex-1 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold shadow-lg shadow-emerald-500/30 transition flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Catat Income
                    </button>
                </div>
            </form>
        </div>

        {{-- Sidebar Tips --}}
        <div class="space-y-6">
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-8 h-8 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <div>
                        <p class="font-bold text-lg mb-2">Semakin Teliti!</p>
                        <p class="text-emerald-100 text-sm">Catat setiap uang yang masuk dengan detail untuk analisis sumber penghasilan terbesar.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Tips Pemasukan
                </h4>
                <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start">
                        <span class="text-emerald-500 mr-2">•</span>
                        <span>Bedakan pemasukan tetap dan tidak tetap</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-emerald-500 mr-2">•</span>
                        <span>Catat sumber penghasilan dengan detail</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-emerald-500 mr-2">•</span>
                        <span>Alokasikan sebagian untuk tabungan</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
