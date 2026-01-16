<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Edit {{ $transaction->type === 'income' ? 'Income 📥' : 'Expense 📤' }}</h1>
            <p class="text-slate-500 text-sm mt-1.5">Perbaiki data transaksimu</p>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <!-- Compact Form Card -->
        <form action="{{ route('transactions.update', $transaction) }}" method="POST" class="bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
            @csrf
            @method('PUT')

            <!-- Form Header -->
            <div class="px-8 py-6 border-b-2 border-slate-200 {{ $transaction->type === 'income' ? 'bg-gradient-to-r from-emerald-50 via-white to-teal-50' : 'bg-gradient-to-r from-red-50 via-white to-orange-50' }}">
                <h2 class="text-xl font-bold text-slate-900">Edit Data {{ ucfirst($transaction->type) }}</h2>
                <p class="text-slate-600 text-sm mt-1">Semua field wajib diisi kecuali yang optional</p>
            </div>

            <!-- Form Body -->
            <div class="p-8 space-y-6">

                <!-- Category - Full Width -->
                <div>
                    <label for="category_id" class="block text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                        <span class="text-lg">📂</span> Kategori <span class="text-red-500">*</span>
                    </label>
                    <select id="category_id" name="category_id"
                        class="w-full px-4 py-3 border-2 rounded-xl focus:ring-0 text-slate-900 bg-white transition font-medium @error('category_id') border-red-500 @else {{ $transaction->type === 'income' ? 'border-emerald-300 focus:border-emerald-600 hover:border-emerald-400' : 'border-red-300 focus:border-red-600 hover:border-red-400' }} @enderror"
                        required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->icon }} {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('category_id')
                    <p class="text-red-600 text-sm mt-2">⚠️ {{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount - FOCAL POINT -->
                <div class="rounded-2xl p-6 border-2 shadow-md {{ $transaction->type === 'income' ? 'bg-gradient-to-br from-emerald-50 to-white border-emerald-400' : 'bg-gradient-to-br from-red-50 to-white border-red-400' }}">
                    <label for="amount" class="block text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <span class="text-2xl">💰</span> Berapa Jumlahnya? <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-700 font-black text-xl">Rp</span>
                        <input type="number" id="amount" name="amount"
                            class="w-full pl-14 pr-5 py-4 border-2 rounded-xl focus:ring-0 text-slate-900 bg-white text-3xl font-black @error('amount') border-red-500 @else {{ $transaction->type === 'income' ? 'border-emerald-500 focus:border-emerald-700' : 'border-red-500 focus:border-red-700' }} @enderror"
                            placeholder="0"
                            step="1"
                            min="1"
                            value="{{ old('amount', intval($transaction->amount)) }}"
                            required>
                    </div>
                    @error('amount')
                    <p class="text-red-600 text-sm mt-2">⚠️ {{ $message }}</p>
                    @enderror
                </div>

                <!-- Date & Payment Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="transaction_date" class="block text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <span>📅</span> Tanggalnya <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="transaction_date" name="transaction_date"
                            class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-slate-500 focus:ring-0 text-slate-900 bg-white hover:border-slate-400 transition font-medium @error('transaction_date') border-red-500 @enderror"
                            value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}"
                            required>
                        @error('transaction_date')
                        <p class="text-red-600 text-sm mt-2">⚠️ {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <span>💳</span> Metode Pembayaran
                        </label>
                        <input type="text" id="payment_method" name="payment_method"
                            class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-slate-500 focus:ring-0 text-slate-900 bg-white hover:border-slate-400 transition"
                            placeholder="Transfer, Cash, E-wallet..."
                            value="{{ old('payment_method', $transaction->payment_method) }}">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                        <span>📝</span> Catatan Tambahan
                    </label>
                    <input type="text" id="description" name="description"
                        class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-slate-500 focus:ring-0 text-slate-900 bg-white hover:border-slate-400 transition"
                        placeholder="Contoh: Detail tambahan..."
                        value="{{ old('description', $transaction->description) }}">
                </div>

            </div>

            <!-- Form Footer -->
            <div class="px-8 py-6 bg-slate-50 border-t-2 border-slate-200 flex gap-3">
                <a href="{{ route('transactions.index') }}"
                    class="flex-1 px-6 py-3 border-2 border-slate-400 rounded-xl font-bold text-slate-700 hover:bg-slate-100 active:scale-95 transition text-center">
                    ← Kembali
                </a>
                <button type="submit"
                    class="flex-1 px-6 py-3 text-white rounded-xl font-bold active:scale-95 transition shadow-lg {{ $transaction->type === 'income' ? 'bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700' : 'bg-gradient-to-r from-red-500 to-orange-600 hover:from-red-600 hover:to-orange-700' }}">
                    ✓ Simpan Perubahan
                </button>
            </div>
        </form>

        <!-- Delete Section -->
        <div class="mt-6 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                <span>⚠️</span> Zona Berbahaya
            </h3>
            <p class="text-slate-600 text-sm mb-4">Menghapus transaksi ini akan mempengaruhi balance walletmu.</p>

            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST"
                onsubmit="return confirm('Yakin ingin menghapus transaksi ini? Balance akan di-update otomatis.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-6 py-3 bg-red-100 text-red-700 rounded-xl font-bold hover:bg-red-200 transition border border-red-200">
                    🗑️ Hapus Transaksi Ini
                </button>
            </form>
        </div>
    </div>
</x-app-layout>