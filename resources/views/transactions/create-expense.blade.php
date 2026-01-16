<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Catat Pengeluaran 📤</h1>
            <p class="text-slate-500 text-sm mt-1.5">Kelola pembelian dan pengeluaran kamu</p>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <!-- Smart Tip Card -->
        <div class="bg-gradient-to-r from-red-500 to-orange-600 rounded-2xl p-6 mb-8 text-white shadow-lg border border-red-400 relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <div class="relative z-10 flex gap-4">
                <span class="text-3xl flex-shrink-0">💡</span>
                <div>
                    <p class="font-bold text-lg">Jangan Lupa Dicatat!</p>
                    <p class="text-red-100 text-sm mt-2">Catat SEMUA pengeluaran, baik besar maupun kecil. Ini kunci untuk memahami kemana uangmu pergi dan membuat rencana finansial yang lebih baik.</p>
                </div>
            </div>
        </div>

        <!-- Compact Form Card -->
        <form action="{{ route('transactions.store') }}" method="POST" class="bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
            @csrf
            <input type="hidden" name="type" value="expense">

            <!-- Form Header -->
            <div class="bg-gradient-to-r from-red-50 via-white to-orange-50 px-8 py-6 border-b-2 border-slate-200">
                <h2 class="text-xl font-bold text-slate-900">Isi Data Pengeluaran</h2>
                <p class="text-slate-600 text-sm mt-1">Semua field wajib diisi kecuali yang optional</p>
            </div>

            <!-- Form Body -->
            <div class="p-8 space-y-6">

                <!-- Category - Full Width -->
                <div>
                    <label for="category_id" class="block text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                        <span class="text-lg">📂</span> Kategori Pengeluaran <span class="text-red-500">*</span>
                    </label>
                    <select id="category_id" name="category_id" 
                            class="w-full px-4 py-3 border-2 border-red-300 rounded-xl focus:border-red-600 focus:ring-0 text-slate-900 bg-white hover:border-red-400 transition font-medium @error('category_id') border-red-500 @enderror" 
                            required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->icon }} {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('category_id')
                    <p class="text-red-600 text-sm mt-2">⚠️ {{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount - FOCAL POINT -->
                <div class="bg-gradient-to-br from-red-50 to-white rounded-2xl p-6 border-2 border-red-400 shadow-md">
                    <label for="amount" class="block text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <span class="text-2xl">💸</span> Berapa Biayanya? <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-700 font-black text-xl">Rp</span>
                        <input type="number" id="amount" name="amount" 
                               class="w-full pl-14 pr-5 py-4 border-2 border-red-500 rounded-xl focus:border-red-700 focus:ring-0 text-slate-900 bg-white text-3xl font-black @error('amount') border-red-500 @enderror"
                               placeholder="0"
                               step="1"
                               min="1"
                               value="{{ old('amount') }}"
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
                               class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-red-500 focus:ring-0 text-slate-900 bg-white hover:border-slate-400 transition font-medium @error('transaction_date') border-red-500 @enderror"
                               value="{{ old('transaction_date', date('Y-m-d')) }}"
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
                               class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-red-500 focus:ring-0 text-slate-900 bg-white hover:border-slate-400 transition"
                               placeholder="Cash, Kartu Debit, E-wallet..."
                               value="{{ old('payment_method') }}">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                        <span>📝</span> Catatan Tambahan
                    </label>
                    <input type="text" id="description" name="description"
                           class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-red-500 focus:ring-0 text-slate-900 bg-white hover:border-slate-400 transition"
                           placeholder="Contoh: Makan siang di kampus, bensin motor..."
                           value="{{ old('description') }}">
                </div>

            </div>

            <!-- Form Footer -->
            <div class="px-8 py-6 bg-slate-50 border-t-2 border-slate-200 flex gap-3">
                <a href="{{ route('dashboard') }}" 
                   class="flex-1 px-6 py-3 border-2 border-slate-400 rounded-xl font-bold text-slate-700 hover:bg-slate-100 active:scale-95 transition text-center">
                    ← Kembali
                </a>
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-orange-600 text-white rounded-xl font-bold hover:from-red-600 hover:to-orange-700 active:scale-95 transition shadow-lg">
                    ✓ Catat Pengeluaran
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
