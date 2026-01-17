@extends('layouts.app')

@section('title', 'Buat Target Tabungan')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <a href="{{ route('savings.index') }}" 
               class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">Buat Target Baru</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Wujudkan impianmu dengan menabung</p>
            </div>
        </div>
    </div>

    {{-- Form Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Form --}}
        <div class="lg:col-span-2">
            <form action="{{ route('savings.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Basic Info Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <span class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
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
                                   value="{{ old('name') }}"
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
                                       value="{{ old('target_amount') }}"
                                       placeholder="0"
                                       min="1000"
                                       class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                       required>
                            </div>
                            @error('target_amount')
                                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Initial Amount --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Dana Awal <span class="text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                                <input type="number" 
                                       name="initial_amount" 
                                       value="{{ old('initial_amount') }}"
                                       placeholder="0"
                                       min="0"
                                       class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Jika sudah ada dana terkumpul</p>
                        </div>

                        {{-- Deadline --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Deadline <span class="text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <input type="date" 
                                   name="deadline" 
                                   value="{{ old('deadline') }}"
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
                            <div class="grid grid-cols-3 gap-2" x-data="{ priority: '{{ old('priority', 'medium') }}' }">
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
                        <div x-data="{ selectedIcon: '{{ old('icon', '🎯') }}' }">
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
                        <div x-data="{ selectedColor: '{{ old('color', '#10b981') }}' }">
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
                                      class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition resize-none">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex flex-col lg:flex-row gap-3">
                    <button type="submit"
                            class="flex-1 py-3 px-6 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-lg shadow-emerald-500/30 transition-all flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Buat Target
                    </button>
                    <a href="{{ route('savings.index') }}"
                       class="lg:w-auto py-3 px-6 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition-all text-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- Sidebar Tips --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    Tips Menabung
                </h4>
                <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-emerald-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Tetapkan target yang realistis dan terukur</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-emerald-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Set deadline untuk memotivasi diri</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-emerald-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Mulai dengan jumlah kecil, tingkatkan bertahap</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-emerald-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Prioritaskan target yang paling penting</span>
                    </li>
                </ul>
            </div>

            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-6 text-white">
                <h4 class="font-semibold mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    Butuh Bantuan?
                </h4>
                <p class="text-sm text-white/80 mb-4">AI Assistant bisa membantu menghitung target tabungan bulanan kamu!</p>
                <a href="{{ route('ai.chat') }}" 
                   class="inline-flex items-center text-sm font-medium text-white hover:underline">
                    Tanya AI
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
