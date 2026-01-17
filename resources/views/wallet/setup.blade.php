<x-guest-layout>
    <div class="text-center mb-8">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Selamat Datang di YourMoment!</h1>
        <p class="text-slate-600 mt-2">Yuk, atur dulu keuanganmu sebelum mulai</p>
    </div>

    <form method="POST" action="{{ route('wallet.setup.store') }}" class="space-y-6">
        @csrf

        <!-- Budget Amount -->
        <div>
            <label for="budget_amount" class="block text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Berapa uang jajanmu?
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
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Uang jajan ini untuk berapa lama?
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative cursor-pointer">
                    <input type="radio" name="budget_type" value="weekly" class="peer sr-only" {{ old('budget_type') === 'weekly' ? 'checked' : '' }} required>
                    <div class="p-4 border-2 border-slate-200 rounded-xl text-center transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:border-emerald-300">
                        <svg class="w-6 h-6 mx-auto mb-1 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="font-bold text-slate-900">Per Minggu</span>
                    </div>
                </label>
                <label class="relative cursor-pointer">
                    <input type="radio" name="budget_type" value="monthly" class="peer sr-only" {{ old('budget_type', 'monthly') === 'monthly' ? 'checked' : '' }}>
                    <div class="p-4 border-2 border-slate-200 rounded-xl text-center transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:border-emerald-300">
                        <svg class="w-6 h-6 mx-auto mb-1 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
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
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                Target tabunganmu berapa?
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
            <p class="text-slate-500 text-sm mt-2 flex items-center gap-1">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                Tentukan target yang ingin kamu capai. Bisa diubah nanti.
            </p>
            @error('financial_goal')
            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit"
            class="w-full py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-bold text-lg hover:from-emerald-600 hover:to-teal-700 transition shadow-lg flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            Mulai Sekarang
        </button>
    </form>
</x-guest-layout>