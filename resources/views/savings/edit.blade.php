@extends('layouts.app')

@section('title', 'Edit Target Tabungan')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <a href="{{ route('savings.show', $goal) }}" 
               class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">Edit Target</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Perbarui informasi target tabunganmu</p>
            </div>
        </div>
    </div>

    {{-- Form Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Form --}}
        <div class="lg:col-span-2">
            <form action="{{ route('savings.update', $goal) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Basic Info Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <span class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </span>
                        Informasi Target
                    </h3>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        {{-- Name Input --}}
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nama Target
                            </label>
                            <input type="text" 
                                   name="name" 
                                   value="{{ old('name', $goal->name) }}"
                                   placeholder="Contoh: Beli iPhone, Liburan Bali, dll"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                   required>
                            @error('name')
                                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Target Amount --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Target Nominal
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                                <input type="number" 
                                       name="target_amount" 
                                       value="{{ old('target_amount', intval($goal->target_amount)) }}"
                                       placeholder="0"
                                       min="{{ intval($goal->current_amount) }}"
                                       class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                       required>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Minimal: Rp {{ number_format($goal->current_amount, 0, ',', '.') }} (sudah terkumpul)</p>
                            @error('target_amount')
                                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Current Amount (Read Only) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Sudah Terkumpul <span class="text-gray-400 font-normal">(tidak bisa diubah)</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                                <input type="text" 
                                       value="{{ number_format($goal->current_amount, 0, ',', '.') }}"
                                       class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                                       disabled>
                            </div>
                        </div>

                        {{-- Deadline --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Deadline <span class="text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <input type="date" 
                                   name="deadline" 
                                   value="{{ old('deadline', $goal->deadline?->format('Y-m-d')) }}"
                                   min="{{ now()->addDay()->toDateString() }}"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                            @error('deadline')
                                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Priority --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Prioritas
                            </label>
                            <div class="grid grid-cols-3 gap-2" x-data="{ priority: '{{ old('priority', $goal->priority ?? 'medium') }}' }">
                                <label class="cursor-pointer">
                                    <input type="radio" name="priority" value="low" x-model="priority" class="sr-only">
                                    <div class="py-2 px-3 rounded-lg border-2 text-center text-sm font-medium transition-all"
                                         :class="priority === 'low' ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-300'">
                                        Rendah
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="priority" value="medium" x-model="priority" class="sr-only">
                                    <div class="py-2 px-3 rounded-lg border-2 text-center text-sm font-medium transition-all"
                                         :class="priority === 'medium' ? 'border-amber-500 bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-300'">
                                        Sedang
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="priority" value="high" x-model="priority" class="sr-only">
                                    <div class="py-2 px-3 rounded-lg border-2 text-center text-sm font-medium transition-all"
                                         :class="priority === 'high' ? 'border-red-500 bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-300'">
                                        Tinggi
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Customization Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <span class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        </span>
                        Kustomisasi
                    </h3>

                    <div class="space-y-4">
                        {{-- Icons --}}
                        <div x-data="{ selectedIcon: '{{ old('icon', $goal->icon) }}' }">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Ikon</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($icons as $icon)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="icon" value="{{ $icon }}" 
                                               x-model="selectedIcon"
                                               class="sr-only">
                                        <span class="w-10 h-10 flex items-center justify-center text-xl rounded-lg border-2 transition-all hover:scale-105"
                                              :class="selectedIcon === '{{ $icon }}' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'">
                                            {{ $icon }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Colors --}}
                        <div x-data="{ selectedColor: '{{ old('color', $goal->color) }}' }">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Warna</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($colors as $color)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color" value="{{ $color }}"
                                               x-model="selectedColor"
                                               class="sr-only">
                                        <span class="w-8 h-8 rounded-full transition-all ring-offset-2 hover:scale-110"
                                              :class="selectedColor === '{{ $color }}' ? 'ring-2 ring-gray-900 dark:ring-white' : ''"
                                              style="background-color: {{ $color }};">
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Catatan <span class="text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <textarea name="description" 
                                      rows="3"
                                      placeholder="Tulis motivasi atau catatan untuk dirimu..."
                                      class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition resize-none">{{ old('description', $goal->description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex flex-col lg:flex-row gap-3">
                    <button type="submit"
                            class="flex-1 py-3 px-6 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-lg shadow-emerald-500/30 transition-all flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('savings.show', $goal) }}"
                       class="lg:w-auto py-3 px-6 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition-all text-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- Sidebar Info --}}
        <div class="space-y-6">
            {{-- Current Progress --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Progress Saat Ini
                </h4>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 dark:text-gray-400">Progress</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $goal->progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                            <div class="h-2.5 rounded-full transition-all" style="width: {{ min($goal->progress, 100) }}%; background-color: {{ $goal->color }};"></div>
                        </div>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Terkumpul</span>
                        <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($goal->current_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Sisa</span>
                        <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($goal->remaining, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Info Box --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5 border border-blue-200 dark:border-blue-800">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-sm">
                        <p class="font-medium text-blue-800 dark:text-blue-300 mb-1">Catatan Penting</p>
                        <ul class="text-blue-700 dark:text-blue-400 space-y-1">
                            <li>Progress yang sudah terkumpul tidak bisa diubah</li>
                            <li>Target baru tidak boleh kurang dari dana terkumpul</li>
                            <li>Deadline harus di masa depan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
