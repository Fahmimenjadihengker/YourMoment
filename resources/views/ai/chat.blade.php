@extends('layouts.app')

@section('title', 'AI Assistant')

@section('content')
<div class="h-full flex" x-data="aiChat()">
    {{-- Main Chat Area --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- Chat Header --}}
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 lg:px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                    <div>
                        <h1 class="text-lg lg:text-xl font-bold text-gray-900 dark:text-white">YourMoment AI</h1>
                        <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400">Financial Assistant</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    {{-- Toggle Context Panel (Desktop) --}}
                    <button @click="showContext = !showContext" 
                            class="hidden lg:flex items-center px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="showContext ? 'Hide Info' : 'Show Info'"></span>
                    </button>
                    {{-- Clear Chat --}}
                    <button @click="clearChat()" 
                            class="flex items-center px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4 lg:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span class="hidden lg:inline">Clear Chat</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Chat Messages --}}
        <div class="flex-1 overflow-y-auto" id="chatContainer" x-ref="chatContainer">
            <div class="max-w-4xl mx-auto px-4 lg:px-6 py-6 space-y-4">
                {{-- Welcome State --}}
                <template x-if="messages.length === 0">
                    <div class="py-8 lg:py-16">
                        {{-- AI Welcome --}}
                        <div class="text-center mb-8">
                            <div class="w-20 h-20 lg:w-24 lg:h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-xl">
                                <svg class="w-10 h-10 lg:w-12 lg:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </div>
                            <h2 class="text-xl lg:text-2xl font-bold text-gray-900 dark:text-white mb-2">Halo! Aku YourMoment AI</h2>
                            <p class="text-gray-600 dark:text-gray-400">Financial assistant pribadimu. Tanya apapun tentang keuanganmu!</p>
                        </div>

                        {{-- Quick Actions Grid --}}
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 max-w-3xl mx-auto">
                            <button @click="sendQuickMessage('Berapa saldo saya sekarang?')" 
                                    class="group p-4 lg:p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow-lg transition-all text-left">
                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white block">Cek Saldo</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">Lihat saldo terkini</span>
                            </button>
                            <button @click="sendQuickMessage('Analisis pengeluaran saya bulan ini')" 
                                    class="group p-4 lg:p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow-lg transition-all text-left">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white block">Analisis Spending</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">Breakdown pengeluaran</span>
                            </button>
                            <button @click="sendQuickMessage('Bagaimana progress target tabungan saya?')" 
                                    class="group p-4 lg:p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow-lg transition-all text-left">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white block">Target Tabungan</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">Progress goals</span>
                            </button>
                            <button @click="sendQuickMessage('Kasih tips hemat untuk mahasiswa dong!')" 
                                    class="group p-4 lg:p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow-lg transition-all text-left">
                                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white block">Tips Hemat</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">Saran menabung</span>
                            </button>
                        </div>

                        {{-- Example Questions --}}
                        <div class="mt-8 max-w-2xl mx-auto">
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-3">Atau coba tanya:</p>
                            <div class="flex flex-wrap justify-center gap-2">
                                <button @click="sendQuickMessage('Berapa total pengeluaran makanan bulan ini?')" 
                                        class="px-3 py-1.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Pengeluaran makanan
                                </button>
                                <button @click="sendQuickMessage('Kapan target tabungan saya tercapai?')" 
                                        class="px-3 py-1.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                    📅 Prediksi tabungan
                                </button>
                                <button @click="sendQuickMessage('Bandingkan pengeluaran bulan ini dengan bulan lalu')" 
                                        class="px-3 py-1.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    Perbandingan bulanan
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Chat Messages --}}
                <template x-for="(msg, index) in messages" :key="index">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        {{-- User Message --}}
                        <template x-if="msg.role === 'user'">
                            <div class="max-w-[80%] lg:max-w-[60%]">
                                <div class="bg-indigo-600 text-white rounded-2xl rounded-br-sm px-4 py-3">
                                    <p class="text-sm whitespace-pre-wrap" x-text="msg.content"></p>
                                </div>
                                <p class="text-xs text-gray-400 mt-1 text-right" x-text="msg.time"></p>
                            </div>
                        </template>
                        {{-- AI Message --}}
                        <template x-if="msg.role === 'assistant'">
                            <div class="max-w-[85%] lg:max-w-[70%] flex space-x-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">
                                        <div class="prose prose-sm dark:prose-invert max-w-none" x-html="formatMessage(msg.content)"></div>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1" x-text="msg.time"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Typing Indicator --}}
                <template x-if="isTyping">
                    <div class="flex justify-start">
                        <div class="flex space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </div>
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-3">
                                <div class="flex space-x-1.5">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 lg:p-6">
            <form @submit.prevent="sendMessage()" class="max-w-4xl mx-auto">
                <div class="flex items-end space-x-3">
                    <div class="flex-1 relative">
                        <textarea 
                            x-model="newMessage"
                            @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                            placeholder="Tanya tentang keuanganmu..."
                            rows="1"
                            class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 border-0 rounded-xl resize-none focus:ring-2 focus:ring-indigo-500 focus:bg-white dark:focus:bg-gray-600 transition-all text-gray-900 dark:text-white placeholder-gray-500 text-sm lg:text-base"
                            :disabled="isTyping"
                            x-ref="messageInput"
                            @input="autoResize($event.target)"
                        ></textarea>
                    </div>
                    <button 
                        type="submit"
                        :disabled="!newMessage.trim() || isTyping"
                        class="flex-shrink-0 w-11 h-11 lg:w-12 lg:h-12 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 dark:disabled:bg-gray-600 text-white rounded-xl flex items-center justify-center transition-colors"
                    >
                        <template x-if="!isTyping">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </template>
                        <template x-if="isTyping">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-2 text-center hidden lg:block">Tekan Enter untuk kirim, Shift+Enter untuk baris baru</p>
            </form>
        </div>
    </div>

    {{-- Context Panel (Desktop Only) --}}
    <div x-show="showContext" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-4"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-4"
         class="hidden lg:block w-80 xl:w-96 flex-shrink-0 bg-gray-50 dark:bg-gray-900 border-l border-gray-200 dark:border-gray-700 overflow-y-auto">
        <div class="p-6 space-y-6">
            {{-- Financial Summary --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ringkasan Keuangan
                </h3>
                <div class="space-y-3">
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Saldo Saat Ini</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($balance ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pemasukan</p>
                            <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">+Rp {{ number_format($monthlyIncome ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pengeluaran</p>
                            <p class="text-sm font-semibold text-red-600 dark:text-red-400">-Rp {{ number_format($monthlyExpense ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Active Goals --}}
            @if(isset($savingGoals) && $savingGoals->count() > 0)
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                    Target Tabungan
                </h3>
                <div class="space-y-2">
                    @foreach($savingGoals->take(3) as $goal)
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $goal->name }}</span>
                            <span class="text-xs text-gray-500">{{ number_format($goal->progress, 0) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                            <div class="bg-indigo-600 h-1.5 rounded-full transition-all" style="width: {{ min($goal->progress, 100) }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Quick Tips --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    Tips Pertanyaan
                </h3>
                <div class="space-y-2 text-xs text-gray-600 dark:text-gray-400">
                    <p class="flex items-start">
                        <svg class="w-4 h-4 mr-2 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <span>"Berapa pengeluaran untuk [kategori]?"</span>
                    </p>
                    <p class="flex items-start">
                        <svg class="w-4 h-4 mr-2 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <span>"Bandingkan income vs expense"</span>
                    </p>
                    <p class="flex items-start">
                        <svg class="w-4 h-4 mr-2 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <span>"Tips untuk mencapai target [nama target]"</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function aiChat() {
    return {
        messages: @json($chatHistory ?? []),
        newMessage: '',
        isTyping: false,
        showContext: true,

        init() {
            this.$nextTick(() => {
                this.scrollToBottom();
            });
        },

        async sendMessage() {
            const message = this.newMessage.trim();
            if (!message || this.isTyping) return;

            const now = new Date();
            const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            
            this.messages.push({
                role: 'user',
                content: message,
                time: time
            });
            this.newMessage = '';
            
            this.$refs.messageInput.style.height = 'auto';
            this.isTyping = true;
            this.scrollToBottom();

            try {
                const response = await fetch('{{ route("ai.send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: message })
                });

                const data = await response.json();
                
                this.messages.push({
                    role: 'assistant',
                    content: data.response,
                    time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                });
            } catch (error) {
                console.error('Error:', error);
                this.messages.push({
                    role: 'assistant',
                    content: 'Maaf, terjadi kesalahan. Coba lagi nanti ya! 😅',
                    time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                });
            } finally {
                this.isTyping = false;
                this.scrollToBottom();
            }
        },

        sendQuickMessage(msg) {
            this.newMessage = msg;
            this.sendMessage();
        },

        clearChat() {
            window.confirmDelete('Hapus semua chat?', 'Riwayat percakapan akan dihapus permanen.').then((result) => {
                if (result.isConfirmed) {
                    this.messages = [];
                    fetch('{{ route("ai.send") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ message: '__clear_history__' })
                    });
                    window.showSuccess('Berhasil', 'Chat berhasil dihapus');
                }
            });
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.chatContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },

        autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        },

        formatMessage(content) {
            return content
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>')
                .replace(/^• /gm, '<span class="inline-block mr-1">•</span>');
        }
    }
}
</script>
@endpush
@endsection
