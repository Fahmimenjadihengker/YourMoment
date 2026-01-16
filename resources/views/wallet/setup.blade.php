<x-guest-layout>
    <div class="text-center mb-8">
        <div class="text-6xl mb-4">🎉</div>
        <h1 class="text-2xl font-bold text-slate-900">Selamat Datang di YourMoment!</h1>
        <p class="text-slate-600 mt-2">Yuk, atur dulu keuanganmu sebelum mulai</p>
    </div>

    <form method="POST" action="{{ route('wallet.setup.store') }}" class="space-y-6">
        @csrf

        <!-- Budget Amount -->
        <div>
            <label for="budget_amount" class="block text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                <span class="text-xl">💰</span> Berapa uang jajanmu?
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-600 font-bold">Rp</span>
                <input type="number"
                    id="budget_amount"
                    name="budget_amount"
                    class="w-full pl-12 pr-4 py-4 border-2 border-emerald-300 rounded-xl focus:border-emerald-500 focus:ring-0 text-slate-900 text-2xl font-bold @error('budget_amount') border-red-500 @enderror"
                    placeholder="500000"
                    min="0"
                    step="1000"
                    value="{{ old('budget_amount') }}"
                    required>
            </div>
            @error('budget_amount')
            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Budget Type -->
        <div>
            <label class="block text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                <span class="text-xl">📅</span> Uang jajan ini untuk berapa lama?
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative cursor-pointer">
                    <input type="radio" name="budget_type" value="weekly" class="peer sr-only" {{ old('budget_type') === 'weekly' ? 'checked' : '' }} required>
                    <div class="p-4 border-2 border-slate-200 rounded-xl text-center transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:border-emerald-300">
                        <span class="text-2xl block mb-1">📆</span>
                        <span class="font-bold text-slate-900">Per Minggu</span>
                    </div>
                </label>
                <label class="relative cursor-pointer">
                    <input type="radio" name="budget_type" value="monthly" class="peer sr-only" {{ old('budget_type', 'monthly') === 'monthly' ? 'checked' : '' }}>
                    <div class="p-4 border-2 border-slate-200 rounded-xl text-center transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:border-emerald-300">
                        <span class="text-2xl block mb-1">🗓️</span>
                        <span class="font-bold text-slate-900">Per Bulan</span>
                    </div>
                </label>
            </div>
            @error('budget_type')
            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Financial Goal (Target Tabungan) -->
        <div>
            <label for="financial_goal" class="block text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                <span class="text-xl">🎯</span> Target tabunganmu berapa?
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-600 font-bold">Rp</span>
                <input type="number"
                    id="financial_goal"
                    name="financial_goal"
                    class="w-full pl-12 pr-4 py-4 border-2 border-amber-300 rounded-xl focus:border-amber-500 focus:ring-0 text-slate-900 text-2xl font-bold @error('financial_goal') border-red-500 @enderror"
                    placeholder="1000000"
                    min="0"
                    step="10000"
                    value="{{ old('financial_goal') }}"
                    required>
            </div>
            <p class="text-slate-500 text-sm mt-2">💡 Tentukan target yang ingin kamu capai. Bisa diubah nanti.</p>
            @error('financial_goal')
            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit"
            class="w-full py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-bold text-lg hover:from-emerald-600 hover:to-teal-700 transition shadow-lg">
            🚀 Mulai Sekarang
        </button>
    </form>
</x-guest-layout>