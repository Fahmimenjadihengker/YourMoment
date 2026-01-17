@extends('layouts.app')

@section('title', $goal->name)

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Page Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div class="flex items-center space-x-4">
            <a href="{{ route('savings.index') }}" 
               class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                    <span class="text-3xl mr-3">{{ $goal->icon }}</span>
                    {{ $goal->name }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    @if($goal->status === 'completed')
                        🎉 Target tercapai!
                    @else
                        {{ $goal->progress }}% tercapai
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            @if($goal->status === 'completed')
                <span class="px-3 py-1.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-lg text-sm font-medium">
                    ✅ Tercapai
                </span>
            @elseif($goal->is_overdue)
                <span class="px-3 py-1.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg text-sm font-medium">
                    ⚠️ Lewat deadline
                </span>
            @endif
            <form action="{{ route('savings.destroy', $goal) }}" method="POST" 
                  onsubmit="return confirm('Yakin mau hapus target ini? Data tidak bisa dikembalikan.')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Progress & Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Progress Card --}}
            <div class="bg-gradient-to-br rounded-2xl p-6 text-white shadow-xl relative overflow-hidden"
                 style="background: linear-gradient(135deg, {{ $goal->color }}dd, {{ $goal->color }});">
                <div class="absolute -top-20 -right-20 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                
                <div class="relative z-10">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                        <div>
                            <p class="text-white/80 text-sm mb-1">Terkumpul</p>
                            <p class="text-4xl lg:text-5xl font-black">Rp {{ number_format($goal->current_amount, 0, ',', '.') }}</p>
                            <p class="text-white/70 text-sm mt-1">
                                dari Rp {{ number_format($goal->target_amount, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-left lg:text-right">
                            <p class="text-6xl lg:text-7xl font-black">{{ $goal->progress }}%</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="w-full bg-white/20 rounded-full h-4 overflow-hidden">
                            <div class="bg-white h-4 rounded-full transition-all duration-1000" 
                                 style="width: {{ min($goal->progress, 100) }}%;"></div>
                        </div>
                        <div class="flex justify-between mt-2 text-sm">
                            <span class="text-white/80">Progress</span>
                            <span class="text-white/80">Sisa: Rp {{ number_format($goal->remaining, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if($goal->deadline)
                        <div class="bg-white/10 backdrop-blur rounded-xl p-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                            <div>
                                <p class="text-white/70 text-xs">Deadline</p>
                                <p class="font-semibold">{{ $goal->deadline->format('d M Y') }}</p>
                            </div>
                            @if(!$goal->is_overdue && $goal->days_remaining)
                                <div class="lg:text-right">
                                    <p class="text-white/70 text-xs">Sisa waktu</p>
                                    <p class="font-semibold">{{ $goal->days_remaining }} hari</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- AI Recommendation --}}
            @if($recommendation)
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-5 border border-indigo-200 dark:border-indigo-800">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white text-xl flex-shrink-0">
                            🤖
                        </div>
                        <div>
                            <h3 class="font-semibold text-indigo-800 dark:text-indigo-300 mb-1">Tips dari AI</h3>
                            <p class="text-indigo-700 dark:text-indigo-400 text-sm leading-relaxed">
                                {{ $recommendation }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Saving Calculator --}}
            @if($goal->deadline && $goal->status === 'active' && !$goal->is_overdue)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                        <span class="text-xl mr-2">🧮</span> Kalkulator Nabung
                    </h3>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        @if($goal->daily_saving_needed)
                            <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 text-center">
                                <p class="text-xs text-emerald-600 dark:text-emerald-400 mb-1">Per Hari</p>
                                <p class="font-bold text-emerald-700 dark:text-emerald-300 text-lg">
                                    Rp {{ number_format($goal->daily_saving_needed, 0, ',', '.') }}
                                </p>
                            </div>
                        @endif
                        @if($goal->weekly_saving_needed)
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 text-center">
                                <p class="text-xs text-blue-600 dark:text-blue-400 mb-1">Per Minggu</p>
                                <p class="font-bold text-blue-700 dark:text-blue-300 text-lg">
                                    Rp {{ number_format($goal->weekly_saving_needed, 0, ',', '.') }}
                                </p>
                            </div>
                        @endif
                        @if($goal->monthly_saving_needed ?? null)
                            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-4 text-center">
                                <p class="text-xs text-purple-600 dark:text-purple-400 mb-1">Per Bulan</p>
                                <p class="font-bold text-purple-700 dark:text-purple-300 text-lg">
                                    Rp {{ number_format($goal->monthly_saving_needed, 0, ',', '.') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Contribution History --}}
            @if($contributions->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                        <span class="text-xl mr-2">📜</span> Riwayat Kontribusi
                    </h3>
                    
                    {{-- Desktop Table --}}
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                    <th class="pb-3">Tanggal</th>
                                    <th class="pb-3">Jumlah</th>
                                    <th class="pb-3">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($contributions as $contribution)
                                    <tr>
                                        <td class="py-3 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $contribution->contributed_at->format('d M Y, H:i') }}
                                        </td>
                                        <td class="py-3 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                            +Rp {{ number_format($contribution->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $contribution->note ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile List --}}
                    <div class="lg:hidden space-y-3">
                        @foreach($contributions as $contribution)
                            <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                                <div>
                                    <p class="font-semibold text-emerald-600 dark:text-emerald-400">
                                        +Rp {{ number_format($contribution->amount, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $contribution->contributed_at->format('d M Y') }}
                                        @if($contribution->note)
                                            • {{ $contribution->note }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Column - Add Funds --}}
        <div class="space-y-6">
            {{-- Add Funds Form --}}
            @if($goal->status === 'active')
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                        <span class="text-xl mr-2">💰</span> Tambah Dana
                    </h3>
                    
                    <form action="{{ route('savings.add-funds', $goal) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        {{-- Quick Amount Buttons --}}
                        <div class="grid grid-cols-2 gap-2" x-data="{ amount: '' }">
                            @foreach([10000, 25000, 50000, 100000] as $quickAmount)
                                <button type="button"
                                        @click="amount = {{ $quickAmount }}; $refs.amountInput.value = {{ $quickAmount }}"
                                        class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition">
                                    +{{ number_format($quickAmount/1000) }}rb
                                </button>
                            @endforeach
                        </div>

                        {{-- Amount Input --}}
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                            <input type="number" 
                                   name="amount" 
                                   x-ref="amountInput"
                                   placeholder="Nominal lainnya..."
                                   min="1000"
                                   class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                   required>
                        </div>
                        @error('amount')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror

                        {{-- Note Input --}}
                        <input type="text" 
                               name="note" 
                               placeholder="Catatan (opsional)"
                               class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">

                        {{-- Submit Button --}}
                        <button type="submit"
                                class="w-full py-3 px-6 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-lg shadow-emerald-500/30 transition-all flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Tambah Dana
                        </button>
                    </form>
                </div>
            @endif

            {{-- Goal Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Detail Target</h4>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Status</span>
                        <span class="font-medium text-gray-900 dark:text-white capitalize">{{ $goal->status }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Prioritas</span>
                        <span class="font-medium text-gray-900 dark:text-white capitalize">{{ $goal->priority ?? 'Medium' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Dibuat</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $goal->created_at->format('d M Y') }}</span>
                    </div>
                    @if($goal->description)
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-gray-600 dark:text-gray-400 mb-1">Catatan:</p>
                            <p class="text-gray-900 dark:text-white">{{ $goal->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Ask AI --}}
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-6 text-white">
                <h4 class="font-semibold mb-2">🤖 Tanya AI</h4>
                <p class="text-sm text-white/80 mb-4">Butuh tips untuk mencapai target ini lebih cepat?</p>
                <a href="{{ route('ai.chat') }}?q=Tips%20untuk%20target%20{{ urlencode($goal->name) }}" 
                   class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                    Tanya Sekarang
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
